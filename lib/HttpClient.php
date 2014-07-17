<?php

/**
 * Description of HttpClient
 *
 * @author Igor
 */
class HttpClient {
    
    const REQUEST_TYPE_GET = 'GET';
    const REQUEST_TYPE_POST = 'POST';
    const REQUEST_TYPE_PUT = 'PUT';
    const REQUEST_TYPE_DELETE = 'DELETE';

    protected $_uri;


    /**
     * http method
     * 
     * @var string
     */
    protected $_method;
    
    /**
     *
     * @var string
     */
    protected $_accept = 'text/xml,application/xml,application/xhtml+xml,text/html,text/plain,image/png,image/jpeg,image/gif,*/*';
    
    /**
     * flag for upload file
     * 
     * @var bool 
     */
    protected $_needUploadFile = false;
    
    /**
     * flag for update content
     * 
     * @var bool 
     */
    protected $_needUploadContent = false; 
    
    /**
     * holds key in parameters for defining which param stores uploading data
     * 
     * @var string 
     */
    protected $_fileKey = 'file';   
    
    /**
     *
     * @var string 
     */
    protected $_status;
    
    /**
     *
     * @var array 
     */
    protected $_headers = array();
    
    /**
     * stores response content
     * 
     * @var string 
     */
    protected $_content = '';
    
    /**
     *
     * @var array 
     */
    protected $_errormsg;
        
    /**
     *  
     * @param string $uri
     * @param int $port
     */
    public function __construct($uri, $port = 80) {
        $this->_uri = $uri;
        $this->setMethod(self::REQUEST_TYPE_GET);
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
     * @return string
     */
    public function getStatus(){
        return $this->_headers['http_code'];
    }

    /**
     * @param resource $ch
     *        curl resource
     * @param int $maxredirect
     *        maximum number of redirects that can be made in order to retrieve url content
     *
     * @return string
     */
    protected function curl_exec_follow($ch, &$maxredirect = null) {
  
      // we emulate a browser here since some websites detect
      // us as a bot and don't let us do our job
      $user_agent = "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.7.5)".
                    " Gecko/20041107 Firefox/1.0";
      curl_setopt($ch, CURLOPT_USERAGENT, $user_agent );

      $mr = $maxredirect === null ? 5 : intval($maxredirect);

      if (ini_get('open_basedir') == '' && ini_get('safe_mode') == 'Off') {

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $mr > 0);
        curl_setopt($ch, CURLOPT_MAXREDIRS, $mr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      } else {
        
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

        if ($mr > 0)
        {
          $original_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
          $newurl = $original_url;
          
          $rch = curl_copy_handle($ch);
          
          curl_setopt($rch, CURLOPT_HEADER, true);
          curl_setopt($rch, CURLOPT_NOBODY, true);
          curl_setopt($rch, CURLOPT_FORBID_REUSE, false);
          do
          {
            curl_setopt($rch, CURLOPT_URL, $newurl);
            $header = curl_exec($rch);
            if (curl_errno($rch)) {
              $code = 0;
            } else {
              $code = curl_getinfo($rch, CURLINFO_HTTP_CODE);
              if ($code == 301 || $code == 302) {
                preg_match('/Location:(.*?)\n/', $header, $matches);
                $newurl = trim(array_pop($matches));
                
                // if no scheme is present then the new url is a
                // relative path and thus needs some extra care
                if(!preg_match("/^https?:/i", $newurl)){
                  $newurl = $original_url . $newurl;
                }   
              } else {
                $code = 0;
              }
            }
          } while ($code && --$mr);
          
          curl_close($rch);
          
          if (!$mr)
          {
            if ($maxredirect === null)
            trigger_error('Too many redirects.', E_USER_WARNING);
            else
            $maxredirect = 0;
            
            return false;
          }
          curl_setopt($ch, CURLOPT_URL, $newurl);
        }
      }
      return curl_exec($ch);
    }

    /**
     * make request
     *
     * @return array
     */
    protected function prepareFileFromString($data) {
        // form field separator
        $delimiter = '-------------' . uniqid();
        // file upload fields: name => array(type=>'mime/type',content=>'raw data')
        $fileFields = array(
            'file' => array(
                'type' => $data['fileType'],
                'content' => $data[$this->_fileKey],
            ),
        );
        // all other fields (not file upload): name => value
        $postFields = $data;
        unset($postFields[$this->_fileKey]);

        $post_data = '';
        // populate normal fields first (simpler)
        foreach ($postFields as $name => $content) {
            $post_data .= "--" . $delimiter . "\r\n";
            $post_data .= 'Content-Disposition: form-data; name="' . $name . '"';
            // note: double endline
            $post_data .= "\r\n\r\n";
            $post_data .= $content . "\r\n";
        }
        // populate file fields
        foreach ($fileFields as $name => $file) {
            $post_data .= "--" . $delimiter . "\r\n";
            // "filename" attribute is not essential; server-side scripts may use it
            $post_data .= 'Content-Disposition: form-data; name="' . $name . '";' .
                ' filename="' . $name . '"' . "\r\n";
            // this is, again, informative only; good practice to include though
            $post_data .= 'Content-Type: ' . $file['type'] . "\r\n";
            // this endline must be here to indicate end of headers
            $post_data .= "\r\n";
            // the file itself (note: there's no encoding of any kind)
            $post_data .= $file['content'] . "\r\n";
        }
        // last delimiter
        $post_data .= "--" . $delimiter . "--\r\n";

        return array('delimiter' => $delimiter, 'data' => $post_data);
    }
    
    /**
     * make request  
     * 
     * @return string
     */
    public function request($data){
        $get_params = '';
        if ($this->_method == self::REQUEST_TYPE_GET || $this->_method == self::REQUEST_TYPE_DELETE) {
            $get_params = '?' . http_build_query($data, NULL, '&');
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->_uri . $get_params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);

        if (in_array($this->_method, array(self::REQUEST_TYPE_POST, self::REQUEST_TYPE_DELETE, self::REQUEST_TYPE_PUT))) {

            if ($this->_needUploadFile || $this->_needUploadContent){
                if ($this->_needUploadFile && file_exists(realpath($data[$this->_fileKey]))){
                    $data['file'] = '@' . realpath($data['file']);
                }

                if ($this->_needUploadContent && ($data[$this->_fileKey] !== '')){
                    $tmp = $this->prepareFileFromString($data);
                    $data = $tmp['data'];
                    $delimiter = $tmp['delimiter'];

                    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                        'Content-Type: multipart/form-data; boundary=' . $delimiter,
                        'Content-Length: ' . strlen($data)));
                }
            }

            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $result = $this->_content = $this->curl_exec_follow($ch);// curl_exec($ch);
        $this->_headers = curl_getinfo($ch);

        if (!$result) {
            $this->_errormsg = 'Error: "' . curl_error($ch) . '" - Code: ' . curl_errno($ch);
        }
        curl_close($ch);

        return $result;
    }
    
    /**
     * 
     * @return string
     */    
    public function getContent(){
        return $this->_content;
    }
    
    /**
     * set flag for uploading file content
     * 
     * @param bool $flag Description
     * @return HttpClient
     */
    public function setNeedUploadFile($flag){
        $this->_needUploadFile = $flag;
        return $this;
    } 
    
    /**
     * set flag for uploading content
     * 
     * @param bool $flag
     * @return \HttpClient
     */
    public function setNeedUploadContent($flag){
        $this->_needUploadContent = $flag;
        return $this;
    }

    public function getError() {
        return $this->_errormsg;
    }
}
