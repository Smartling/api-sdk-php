<?php
 
require_once("HttpClient.php");
 
class SmartlingAPI
{   
    const SANDBOX_MODE = 'SANDBOX';
    const PRODUCTION_MODE = 'PRODUCTION';
    
    /**
     *api base url
     * 
     * @var string 
     */
    protected $_baseUrl = ""; 
    
    /**
     *
     * @var string 
     */
    protected $_apiKey;
    
    /**
     *
     * @var string
     */
    protected $_projectId;
    
    /**
     *
     * @var null | string 
     */
    protected $_response = null;
 
    public function __construct($apiKey, $projectId, $mode = self::SANDBOX_MODE) {
        $this->_apiKey = $apiKey;
        $this->_projectId = $projectId;
        if ($mode == self::PRODUCTION_MODE){
            $this->_baseUrl = "https://api.smartling.com/v1";
        } else {
            $this->_baseUrl = "https://sandbox-api.smartling.com/v1";
        }
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
        return $this->sendRequest('file/upload', array_merge_recursive(array(
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
        return $this->sendRequest('file/get', array_merge_recursive(array(
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
        return $this->sendRequest('file/status', array_merge_recursive(array(
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
        return $this->sendRequest('file/list', array_merge_recursive(array(
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
        return $this->sendRequest('file/rename', array(
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
        return $this->sendRequest('file/delete', array(
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
        
        return $this->sendRequest('file/import', array(
            'fileUri'          => $fileUri,
            'fileType'         => $fileType,
            'locale'           => $locale,
            'file'             => $file,
            'overwrite'        => $overwrite,
            'translationState' => $translationState,
        ), HttpClient::REQUEST_TYPE_POST, true);
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
            return $this->_response = $connection->getContent();
        } else {
            return new Exception("Can't connect to server");
        }
    }
    
    /**
     * 
     * @return boolean | string
     */
    public function getCodeStatus(){
        if (!is_null($this->_response)){
            if ($result = json_decode($this->_response)){
                return $result->response->code;
            }
        } else {
            return false;
        }
    }
}
