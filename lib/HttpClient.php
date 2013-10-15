<?php

/**
 * Description of HttpClient
 *
 * @author Igor
 */
class HttpClient {
    
    protected $_host; 
    protected $_port; 
    protected $_path;
    protected $_scheme;
    protected $_method;
    protected $_postdata = '';    
    protected $_httpVersion = 'HTTP/1.0';
    protected $_accept = 'text/xml,application/xml,application/xhtml+xml,text/html,text/plain,image/png,image/jpeg,image/gif,*/*';
    protected $_acceptEncoding = 'gzip';     
    protected $_requestHeaders = array();
    protected $_requestData;
    protected $_timeout = 30;
    protected $_useGzip = false;    
    protected $_maxRedirects = 5;
    protected $_headersOnly = false;
    protected $_file;
    
    protected $_status;
    protected $_headers = array();
    protected $_content = '';
    protected $_errormsg;

    // * Tracker variables:

    protected $_redirect_count = 0;
    
    /**
     * 
     * @param string $uri
     * @param int $port
     */
    public function __construct($uri, $port = 80) {
        $parsedUrl = parse_url($uri);
        $this->_host = $parsedUrl['host'];
        $this->_scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] : 'http';
        $this->_path = isset($parsedUrl['path']) ? $parsedUrl['path'] : "/";
        $this->_port = isset($parsedUrl['port']) ? $parsedUrl['port'] : $port;
    }
    
    /**
     * 
     * @param string $method
     * @return \HttpClient
     */
    public function setMethod($method){
        $this->_method = $method;
        return $this;
    }
    
    /**
     * 
     * @param array $headers
     */
    public function setHeaders($headers){
        if (is_array($headers)){
            foreach ($headers as $key => $value) {
                $this->_requestHeaders[$key] = $value;
            }
        }
    }
    
    /**
     * 
     * @return int
     */
    public function getStatus(){
        return $this->_status;
    }
    
    /**
     * make request  
     * 
     * @return boolean
     */
    public function request(){
        $fp = $this->_connect();
        if ($fp){
            socket_set_timeout($fp, $this->_timeout);
            $request = $this->_buildRequest();
            @fwrite($fp, $request);
            $this->_retrieveResponse($fp);
            fclose($fp);
            
            if (isset($this->_headers['content-encoding']) && $this->_headers['content-encoding'] == 'gzip') {              
                $this->_content = substr($this->_content, 10);
                $this->_content = gzinflate($this->_content);
            }
        }
        return true;
        
    }
    
    /**
     * 
     * @param string $data
     */
    public function setPostData($data){
        $this->_postdata = $data;
    }
    
    /**
     * 
     * @param string | array | object $data
     * @return \HttpClient
     */
    public function setRequestData($data){
        if (is_array($data) || is_object($data) || is_string($data)){
            $this->_requestData = $data;
        } else {
            throw new Exception("Uncorrect parameters data type");
        }        
        return $this;
    }
    
    /**
     * 
     * @return string | array | object
     */
    public function getRequestData(){
        return $this->_requestData;
    }


    /**
     * 
     * @param string | array | object $data
     */
    protected function _buildQuery($data){
        if (is_array($data) || is_object($data)){
            $data = http_build_query($data);
        }
        if ($this->_method == "POST"){
            $this->_postdata = $data;
        }        
        return $data;
    }
    
    /**
     * set http headers for request
     * 
     * @return string
     */
    protected function _buildRequest(){
        $headers = array();
        $data = $this->getRequestData();
        $query = $this->_buildQuery($data);
        if (!empty($data) && $this->_method == 'GET'){
            $path = $this->_path . "?" . $query;
        } else {            
            $path = $this->_path;
        }      
        
        //var_dump($this->_postdata);
        //exit();
        $headers[] = "{$this->_method} {$path} {$this->_httpVersion}"; // * Using 1.1 leads to all manner of problems, such as "chunked" encoding
        $headers[] = "Host: {$this->_host}";
        
        //set request headers defined earlier
        if(!empty($this->_requestHeaders)) {
            foreach($this->_requestHeaders as $key => $val) {
                if($val===false) {
                    continue;
                } else {
                    $headers[] = $key.': '.$val;
                }
            }
        }
        
        if ($this->_useGzip && !isset($this->_requestHeaders['Accept-encoding']))
            $headers[] = "Accept-encoding: {$this->_acceptEncoding}";

        // If it is a POST, add Content-Type.
        if (!isset($this->_requestHeaders['Content-Type']) &&
            $this->_method == 'POST') {
            $headers[] = "Content-Type: multipart/from-data";
        }
        
        /*
        if (!isset($this->_requestHeaders['User-Agent']))
            $headers[] = "User-Agent: {$this->_userAgent}";
        */
        
        if (!isset($this->_requestHeaders['Accept']))
            $headers[] = "Accept: {$this->_accept}";
        if ($this->_method == "POST"){
            if ($this->_postdata && !isset($this->_requestHeaders['Content-Length'])) {
                $headers[] = 'Content-Length: '.strlen($this->_postdata);
            }
        }
        
//        if ($this->_file){
//            $data = file_get_contents($this->_file);
//            $requestPost = "\r\n";
//            $requestPost .= "Content-Disposition: form-data; name=\"{$userfile}\"; filename=\"{$this->_file}\"\r\n";
//        }
        
        //var_dump($headers);
        //exit();
        $request = implode("\r\n", $headers) . "\r\n\r\n" . $this->_postdata;
        return $request;   
    }
    
    /**
     * 
     * @return resource
     */
    protected function _connect(){
        if($this->_scheme == 'https') {
            $host = 'ssl://' . $this->_host;
            $this->_port = 443;
        } else {
            $host = $this->_host;
        }
        
        $fp = @fsockopen($host, $this->_port, $errno, $errstr, $this->_timeout); 
        if (!$fp){
            throw new Exception($errstr);
        }
        return $fp;
    }
    
    /**
     * 
     * @param resource $fp
     * @return boolean
     */
    protected function _retrieveResponse($fp){
        $startAction = true;
        $defHeaders = true;
        
        while (!feof($fp)){
            $line = fgets($fp, 4096);
            //echo $line;
            if ($startAction) {
                $startAction = false;

                if (!preg_match('/HTTP\/(\\d\\.\\d)\\s*(\\d+)\\s*(.*)/', $line, $m)) {
                    $this->_errormsg = "Status code line invalid: ".htmlentities($line);                    
                    return false;
                }
                
                $this->_status = $m[2];
                continue;
            }
            
            if ($defHeaders) {

                if (trim($line) == '') {
                    $defHeaders = false;                    
                    if ($this->_headersOnly) {
                        break; //Skip the rest of the input
                    }
                    continue;
                }

                if (!preg_match('/([^:]+):\\s*(.*)/', $line, $m)) {
                    // Skip to the next header:
                    continue;
                }

                $key = strtolower(trim($m[1]));
                $val = trim($m[2]);

                if (isset($this->_headers[$key])) {
                    if (is_array($this->_headers[$key])) {
                        $this->_headers[$key][] = $val;
                    } else {
                        $this->_headers[$key] = array($this->_headers[$key], $val);
                    }
                } else {
                    $this->_headers[$key] = $val;
                }
                continue;
            }
            $this->_content .= $line;            
        }
    }
    
    /**
     * 
     * @return string
     */    
    public function getContent(){
        return $this->_content;
    }
    
    /**
     * set file
     * 
     * @return HttpClient
     */
    public function uploadFile($file){
        $this->_file = $file;
        return $this;
    }
}
