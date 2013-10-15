<?php
 
require_once("HttpClient.php");
 
class SmartlingAPI
{
    protected $_baseUrl = "https://sandbox-api.smartling.com/v1/file";
 
    protected $_apiKey;
    protected $_projectId;
 
    public function __construct($apiKey, $projectId) {
        $this->_apiKey = $apiKey;
        $this->_projectId = $projectId;
    }
    
    /**
     * 
     * @param string $path
     * @param string $fileType
     * @param string $fileUri
     * @param string $charset
     * @return string
     */
    public function uploadFile($path, $fileType, $fileUri, $charset = 'utf-8') {        
        return $this->sendRequest('upload', array(
            //'file' => $path . ';type=text/xml charset=' . $charset,
            'file'     => $path,
            'fileType' => $fileType,
            'fileUri'  => $fileUri
        ), 'POST');
    }
    
    /**
     * 
     * @param string $fileUri
     * @param string $locale
     * @return string
     */
    public function downloadFile($fileUri, $locale) {
        return $this->sendRequest('get', array(
            'fileUri' => $fileUri,
            'locale' => $locale
        ), 'GET');
    }
    
    /**
     * 
     * @param string $fileUri
     * @param string $locale
     * @return string
     */
    public function getStatus($fileUri, $locale) {
        return $this->sendRequest('status', array(
            'fileUri' => $fileUri,
            'locale' => $locale
        ), 'GET');
    }
    
    /**
     * 
     * @param string $locale
     * @param array $params
     * @return string
     */
    public function getList($locale, $params = array()) {
        return $this->sendRequest('list', array_merge_recursive(array(
            'locale' => $locale
        ), $params), 'GET');
    }
    
    /**
     * 
     * @param string $fileUri
     * @param string $newFileUri
     * @return string
     */
    public function renameFile($fileUri, $newFileUri){
        return $this->sendRequest('rename', array(
            'fileUri' => $fileUri,
            'newFile' => $newFileUri,
        ), 'POST');
    }
    
    /**
     * 
     * @param string $fileUri
     * @return string
     */
    public function deleteFile($fileUri){
        return $this->sendRequest('delete', array(
            'fileUri' => $fileUri,
        ), 'DELETE');
    }
    
    /**
     * 
     * @param string $fileUri
     * @param string $fileType
     * @param string $locale
     * @param string $file
     * @param string $overwrite
     * @param string $translationState
     * @return string
     */
    public function import($fileUri, $fileType, $locale, $file, $overwrite = false, $translationState){
        return $this->sendRequest('import', array(
            'fileUri'          => $fileUri,
            'fileType'         => $fileType,
            'locale'           => $locale,
            'file'             => $file,
            'overwrite'        => $overwrite,
            'translationState' => $translationState,
        ), 'POST');
    }
    
    /**
     * 
     * @param string $uri
     * @param array $requestData
     * @param string $method
     * @return string
     */
    protected function sendRequest($uri, $requestData, $method) {
        $connection = new HttpClient($this->_baseUrl. "/" . $uri, 443);
        
        $data['apiKey'] = $this->_apiKey;
        $data['projectId'] = $this->_projectId;
        
        if (!empty($requestData)){
            foreach ($requestData as $key => $value) {
                if ($key == 'file'){
                    $value = $this->_fileUpload($value, $requestData['fileUri'], $connection);
                }
                $data[$key] = $value;
            }
        }
        
//        var_dump($data);
//        exit();
        $connection->setMethod($method)
                   ->setRequestData($data);
        if($connection->request())
        {
            return $connection->getContent();
        }
        else
        {
            return new Exception("No connection");
        }
    }
    
    /**
     * 
     * @param string $file
     * @param string $fileUri
     * @param HttpClient $connection
     * @return string
     */
    protected function _fileUpload($file, $fileUri, HttpClient $connection){
        $boundary = '---HTTPCLIENT-' . md5(microtime());
        $fileUr = $fileUri;
        $headers = array(
            "Content-Type" => "multipart/form-data; boundary={$boundary}",
            );
        $connection->setHeaders($headers);
        $contentData = file_get_contents(realpath($file));
        $content = "\r\n" . $boundary . "\r\n";
        //$content .= "Content-Disposition: form-data; name=\"file\"; filename=\"{$fileUri}\"\r\n";
        //$content .= "Content-type:text/xml\r\n\r\n";
        //$content .= $contentData;
        $content .= '@' . realpath($file) . ";type=text/plain charset=utf-8";
        $content .= "\r\n" . $boundary . "\r\n";
        return $content;
    }
}
