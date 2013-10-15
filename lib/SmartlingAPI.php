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
    public function uploadFile($path, $fileType, $fileUri, $params = array()) {        
        return $this->sendRequest('upload', array_merge_recursive(array(
            //'file'     => $path,
            'file'     => $path,
            'fileType' => $fileType,
            'fileUri'  => $fileUri
        ), $params), HttpClient::REQUEST_TYPE_POST, true);
    }
    
    /**
     * 
     * @param string $fileUri
     * @param string $locale
     * @return string
     */
    public function downloadFile($fileUri, $locale, $params = array()) {
        return $this->sendRequest('get', array_merge_recursive(array(
            'fileUri' => $fileUri,
            'locale' => $locale
        ), $params), HttpClient::REQUEST_TYPE_GET);
    }
    
    /**
     * 
     * @param string $fileUri
     * @param string $locale
     * @return string
     */
    public function getStatus($fileUri, $locale, $params = array()) {
        return $this->sendRequest('status', array_merge_recursive(array(
            'fileUri' => $fileUri,
            'locale' => $locale
        ), $params), HttpClient::REQUEST_TYPE_GET);
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
        ), $params), HttpClient::REQUEST_TYPE_GET);
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
            'newFileUri' => $newFileUri,
        ), HttpClient::REQUEST_TYPE_POST);
    }
    
    /**
     * 
     * @param string $fileUri
     * @return string
     */
    public function deleteFile($fileUri){
        return $this->sendRequest('delete', array(
            'fileUri' => $fileUri,
        ), HttpClient::REQUEST_TYPE_DELETE);
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
        ), HttpClient::REQUEST_TYPE_POST);
    }
    
    /**
     * 
     * @param string $uri
     * @param array $requestData
     * @param string $method
     * @return string
     */
    protected function sendRequest($uri, $requestData, $method, $needUploadFile = false) {
        $connection = new HttpClient($this->_baseUrl. "/" . $uri, 443);
        
        $data['apiKey'] = $this->_apiKey;
        $data['projectId'] = $this->_projectId;
        
        $request = array_merge_recursive($data, $requestData);
                
        $connection->setMethod($method)                    
                   ->setRequestData($request)
                   ->setNeedUploadFile($needUploadFile);
                   
        
        if($connection->request())
        {
            return $connection->getContent();
        } else {
            return new Exception("No connection");
        }
    }  
}
