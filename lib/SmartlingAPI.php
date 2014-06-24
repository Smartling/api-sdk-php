<?php

require_once("HttpClient.php");
require_once("FileUploadParameterBuilder.php");

class SmartlingAPI {

  const SANDBOX_MODE = 'SANDBOX';
  const PRODUCTION_MODE = 'PRODUCTION';
  const SANDBOX_URL = 'https://sandbox-api.smartling.com/v1';
  const PRODUCTION_URL = 'https://api.smartling.com/v1';

  /**
   * api base url
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

  public function __construct($baseUrl, $apiKey, $projectId, $mode = self::SANDBOX_MODE) {
    $this->_apiKey = $apiKey;
    $this->_projectId = $projectId;
    if ($mode == self::PRODUCTION_MODE) {
      if (!empty($baseUrl)) {
        $this->_baseUrl = $baseUrl;
      }
      else {
        $this->_baseUrl = self::PRODUCTION_URL;
      }
    }
    else {
      $this->_baseUrl = self::SANDBOX_URL;
    }
  }

  /**
   * upload file to Smartling service
   *
   * @param string $path
   * @param array $params
   * @return string
   */
  public function uploadFile($path, $params = array()) {
  	$params['file'] = $path;
    return $this->sendRequest('file/upload', $params, HttpClient::REQUEST_TYPE_POST, true);
  }

  /**
   * upload content to Smartling service
   *
   * @param string $content
   * @param array $params
   * @return string
   */
  public function uploadContent($content, $params = array()) {
    $params['file'] = $content;
    return $this->sendRequest('file/upload', $params, HttpClient::REQUEST_TYPE_POST, false, true);
  }

  /**
   * download translated content from Smartling Service
   *
   * @param string $fileUri
   * @param string $locale
   * @return string
   */
  public function downloadFile($fileUri, $locale, $params = array()) {
    return $this->sendRequest('file/get', array_replace_recursive(array(
          'fileUri' => $fileUri,
          'locale' => $locale
                ), $params), HttpClient::REQUEST_TYPE_GET);
  }

  /**
   * retrieve status about file translation progress
   *
   * @param string $fileUri
   * @param string $locale
   * @return string
   */
  public function getStatus($fileUri, $locale, $params = array()) {
    return $this->sendRequest('file/status', array_replace_recursive(array(
          'fileUri' => $fileUri,
          'locale' => $locale
                ), $params), HttpClient::REQUEST_TYPE_GET);
  }

  /**
   * get uploaded files list
   *
   * @param string $locale
   * @param array $params
   * @return string
   */
  public function getList($locale = '', $params = array()) {
    $params = (empty($locale)) ? $params : array_replace_recursive(array('locale' => $locale), $params);
    return $this->sendRequest('file/list', $params, HttpClient::REQUEST_TYPE_GET);
  }

  /**
   * rename uploaded before files
   *
   * @param string $fileUri
   * @param string $newFileUri
   * @return string
   */
  public function renameFile($fileUri, $newFileUri) {
    return $this->sendRequest('file/rename', array(
          'fileUri' => $fileUri,
          'newFileUri' => $newFileUri,
            ), HttpClient::REQUEST_TYPE_POST);
  }

  /**
   * remove uploaded files from Smartling Service
   *
   * @param string $fileUri
   * @return string
   */
  public function deleteFile($fileUri) {
    return $this->sendRequest('file/delete', array(
          'fileUri' => $fileUri,
            ), HttpClient::REQUEST_TYPE_DELETE);
  }

  /**
   * import files form Service
   *
   * @param string $fileUri
   * @param string $fileType
   * @param string $locale
   * @param string $file
   * @param string $overwrite
   * @param string $translationState
   * @return string
   */
  public function import($fileUri, $fileType, $locale, $file, $overwrite = false, $translationState) {

    return $this->sendRequest('file/import', array(
          'fileUri' => $fileUri,
          'fileType' => $fileType,
          'locale' => $locale,
          'file' => $file,
          'overwrite' => $overwrite,
          'translationState' => $translationState,
            ), HttpClient::REQUEST_TYPE_POST, true);
  }

  /**
   * send request to Smartling Service
   *
   * @param string $uri
   * @param array $requestData
   * @param string $method
   * @return string
   */
  protected function sendRequest($uri, $requestData, $method, $needUploadFile = false, $needUploadContent = false) {
    $connection = new HttpClient($this->_baseUrl . "/" . $uri, 443);

    $data['apiKey'] = $this->_apiKey;
    $data['projectId'] = $this->_projectId;

    $request = array_replace_recursive($data, $requestData);

    $connection->setMethod($method)
        ->setNeedUploadFile($needUploadFile)
        ->setNeedUploadContent($needUploadContent);


    if ($res = $connection->request($request)) {
      return $this->_response = $connection->getContent();
    }
    else {
      return new Exception("Can't connect to server");
    }
  }

  /**
   *
   * @return boolean | string
   */
  public function getCodeStatus() {
    if (!is_null($this->_response)) {
      if ($result = json_decode($this->_response)) {
        return $result->response->code;
      }
    }
    else {
      return false;
    }
  }

}
