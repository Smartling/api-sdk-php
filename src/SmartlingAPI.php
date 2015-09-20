<?php

namespace Smartling;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;

class SmartlingApi {

  const SANDBOX_MODE = 'SANDBOX';
  const PRODUCTION_MODE = 'PRODUCTION';
  const SANDBOX_URL = 'https://sandbox-api.smartling.com/v1/';
  const PRODUCTION_URL = 'https://api.smartling.com/v1/';

  /**
   * Smartling API base url.
   *
   * @var string
   */
  protected $baseUrl;

  /**
   * Smartling API key.
   *
   * @var string
   */
  protected $apiKey;

  /**
   * Smartling API project ID.
   *
   * @var string
   */
  protected $projectId;

  /**
   * Http Client abstraction.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  public function __construct($baseUrl, $apiKey, $projectId, ClientInterface $http_client, $mode = self::SANDBOX_MODE) {
    $this->apiKey = $apiKey;
    $this->projectId = $projectId;
    if ($mode == self::PRODUCTION_MODE) {
      $this->baseUrl = !empty($baseUrl) ? $baseUrl : self::PRODUCTION_URL;
    }
    else {
      $this->baseUrl = self::SANDBOX_URL;
    }

    $this->httpClient = $http_client;

  }

  /**
    * Get locale list for project.
    *
    * @return string
    */
  public function getLocaleList() {
    return $this->sendRequest('project/locale/list', [], 'GET');
  }

  /**
   * Uploads original source content to Smartling.
   *
   * @param string $realPath
   *   Real path to the file to read in into stream.
   * @param string $file_name
   *   Value that uniquely identifies the uploaded file. This ID can be used to
   *   request the file back.
   * @param string $file_type
   *   Unique identifier for the file type. Permitted values: android, ios,
   *   gettext, html, javaProperties, yaml, xliff, xml, json, docx, pptx, xlsx,
   *   idml, qt, resx, plaintext, cvs, stringsdict.
   * @param array $params
   *   (optional) An associative array of additional options, with the following
   *   elements:
   *   - 'approved': Determines whether content in the file is authorized
   *     (available for translation) upon submitting the file via the Smartling
   *     Dashboard.
   *   - 'localesToApprove': This value, if set, authorizes strings for
   *     translation into specific locales.
   *
   * @return \GuzzleHttp\Psr7\Response
   *
   * @throws \Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   *
   * @see http://docs.smartling.com/pages/API/FileAPI/Upload-File/
   */
  public function uploadFile($realPath, $file_name, $file_type, $params = array()) {
    $params['file'] = $realPath;
    $params['fileUri'] = $file_name;
    $params['fileType'] = $file_type;
    return $this->sendRequest('file/upload', $params, 'POST', true);
  }

  /**
   * Uploads original source content to Smartling.
   *
   * @param string $content
   * @param array $params
   * @return string
   */
  public function uploadContent($content, $params = array()) {
    $params['file'] = $content;
    return $this->sendRequest('file/upload', $params, 'POST', false, true);
  }

  /**
   * Downloads the requested file from Smartling.
   *
   * It is important to check the HTTP response status code. If Smartling finds
   * and returns the file normally, you will receive a 200 SUCCESS response.
   * If you receive any other response status code than 200, the requested
   * file will not be part of the response.
   *
   * @param string $fileUri
   *   Value that uniquely identifies the downloaded file.
   * @param string $locale
   *   A locale identifier as specified in project setup. If no locale
   *   is specified, original content is returned.
   * @param array $params
   *   (optional) An associative array of additional options, with the following
   *   elements:
   *   - 'retrievalType': Determines the desired format for the download. Could
   *     be one of following values:
   *     pending|published|pseudo|contextMatchingInstrumented
   *   - 'includeOriginalStrings': Boolean that specifies whether Smartling will
   *     return the original string or an empty string where no translation
   *     is available.
   *
   * @return \GuzzleHttp\Psr7\Response
   *   Object that will contain translated file binary data in body.
   *
   * @throws \Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   *
   * @see http://docs.smartling.com/pages/API/FileAPI/Download-File/
   */
  public function downloadFile($fileUri, $locale = '', $params = []) {
    $params['fileUri'] = $fileUri;
    $params['locale'] = $locale;
    return $this->sendRequest('file/get', $params, 'GET');
  }

  /**
   * Retrieves status about file translation progress.
   *
   * @param string $fileUri
   *   Value that uniquely identifies the file.
   * @param string $locale
   *   A locale identifier as specified in project setup.
   * @param array $params
   *
   * @return \Psr\Http\Message\ResponseInterface
   *
   * @throws \Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   *
   * @see http://docs.smartling.com/pages/API/FileAPI/Status/
   */
  public function getStatus($fileUri, $locale, $params = []) {
    $params['fileUri'] = $fileUri;
    $params['locale'] = $locale;
    return $this->sendRequest('file/status', $params, 'GET');
  }

  /**
   * Lists recently uploaded files. Returns a maximum of 500 files.
   *
   * @param string $locale
   *   If not specified, the Smartling Files API will return a listing of the
   *   original files matching the specified criteria. When the locale is not
   *   specified, completedStringCount will be "0".
   * @param array $params
   *   (optional) An associative array of additional options, with the following
   *   elements:
   *   - 'uriMask': Returns only files with a URI containing a given string.
   *     Case is ignored and % is a wildcard. For example, the value .js%n will
   *     match strings.json and STRINGS.JSON but not json.strings.
   *   - 'fileTypes': Identifiers: android, ios, gettext, html, javaProperties,
   *     yaml, xliff, xml, json, docx, pptx, xlsx, idml, qt, resx, plaintext,
   *     cvs. File types are combined using the logical "OR".
   *   - 'lastUploadedAfter': Returns all files uploaded after the specified
   *     date.
   *   - 'lastUploadedBefore': Returns all files uploaded before the specified
   *     date.
   *   - 'offset': For result set returns, the offset is a number indicating the
   *     distance from the beginning of the list; for example, for a result set
   *     of "50" files, you can set the offset at 10 to return files 10 - 50.
   *   - 'limit': For result set returns, limits the number of files returned;
   *     for example, for a result set of 50 files, a limit of "10" would
   *     return files 0 - 10.
   *   - 'conditions': An array of the following conditions:
   *     haveAtLeastOneUnapproved, haveAtLeastOneApproved,
   *     haveAtLeastOneTranslated, haveAllTranslated, haveAllApproved,
   *     haveAllUnapproved. Conditions are combined using the logical "OR".
   *   - 'orderBy': Sets the name of the parameter to order results by: fileUri,
   *     stringCount, wordCount, approvedStringCount, completedStringCount,
   *     lastUploaded and fileType. You can specify ascending or descending with
   *     each parameter by adding "_asc" or "_desc"; for example,
   *     "fileUri_desc". If you do not specify ascending or descending, the
   *     default is ascending.
   *
   * @return \GuzzleHttp\Psr7\Response
   *   List of files objects.
   *
   * @throws \Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   *
   * @see http://docs.smartling.com/pages/API/FileAPI/List/
   */
  public function getList($locale = '', $params = []) {
    $params['locale'] = $locale;
    return $this->sendRequest('file/list', $params, 'GET');
  }

  /**
   * Renames an uploaded file by changing the fileUri.
   *
   * After renaming the file, the file will only be identified by the new
   * fileUri you provide.
   *
   * @param string $fileUri
   *   Current value that uniquely identifies the file.
   * @param string $newFileUri
   *   The new value for fileUri. We recommend that you use file path + file
   *   name, similar to how version control systems identify the file.
   * @param array $params
   *
   * @return \Psr\Http\Message\ResponseInterface
   *
   * @throws \Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   *
   * @see http://docs.smartling.com/pages/API/FileAPI/Rename/
   */
  public function renameFile($fileUri, $newFileUri, $params = []) {
    $params['fileUri'] = $fileUri;
    $params['newFileUri'] = $newFileUri;
    return $this->sendRequest('file/rename', $params, 'POST');
  }

  /**
   * Removes the file from Smartling.
   *
   * The file will no longer be available for download. Any complete
   * translations for the file remain available for use within the system.
   * Smartling deletes files asynchronously and it typically takes a few minutes
   * to complete. While deleting a file, you can not upload a file with the
   * same fileUri.
   *
   * @param string $fileUri
   *   Value that uniquely identifies the file.
   *
   * @return string
   */
  public function deleteFile($fileUri) {
    return $this->sendRequest('file/delete', ['fileUri' => $fileUri], 'DELETE');
  }

  /**
   * Import files form Service.
   *
   * @param string $fileUri
   *   The Smartling URI for file that contains the original language strings
   *   already uploaded to Smartling.
   * @param string $fileType
   *   The type of file used for imports. Valid values are: ios, android,
   *   gettext, javaProperties, xml, json, yaml, and csv.
   * @param string $locale
   *   The Smartling locale identifier for the language Smartling is importing.
   * @param string $fileRealPath
   *   Absolute path to the file on your local machine that contains the
   *   translated content,
   * @param string $translationState
   *   Value indicating the workflow state to import the translations into.
   *   Content will be imported into the language's default workflow.
   *   Could be 'PUBLISHED' or 'POST_TRANSLATION'.
   * @param array $params
   *   (optional) An associative array of additional options, with the following
   *   elements:
   *   - 'overwrite': Boolean indicating whether or not to overwrite existing
   *     translations.
   *
   * @return string
   *
   * @throws \Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   *
   * @see http://docs.smartling.com/pages/API/Translation-Imports/
   */
  public function import($fileUri, $fileType, $locale, $fileRealPath, $translationState, $params = []) {
    $params['fileUri'] = $fileUri;
    $params['fileType'] = $fileType;
    $params['locale'] = $locale;
    $params['file'] = $fileRealPath;
    $params['translationState'] = $translationState;
    return $this->sendRequest('file/import', $params, 'POST', true);
  }

  /**
   * send request to Smartling Service
   *
   * @param string $uri
   * @param array $requestData
   * @param string $method
   * @param bool $needUploadFile
   * @param bool $needUploadContent
   * @return \Psr\Http\Message\ResponseInterface
   * @throws \Exception
   */
  protected function sendRequest($uri, $requestData, $method, $needUploadFile = false, $needUploadContent = false) {
    $requestData['apiKey'] = $this->apiKey;
    $requestData['projectId'] = $this->projectId;
    $options = [
      'headers' => [
        'Accept' => 'application/json',
      ],
      'http_errors' => FALSE,
    ];

    if (in_array($method, ['GET', 'DELETE'])) {
      $options['query'] = $requestData;
    }
    elseif ($needUploadFile) {
      $options['multipart'] = [];
      // Remove file from params array to add it manually later.
      $file = $requestData['file'];
      unset($requestData['file']);
      foreach ($requestData as $key => $value) {
        $options['multipart'][] = [
          'name' => $key,
          // Typecast everything to string to avoid curl notices.
          'contents' => (string) $value,
        ];
      }

      // Separate handling for file content.
      $options['multipart'][] = [
        'name' => 'file',
        'contents' => fopen($file, 'r'),
      ];
    }
    else {
      $options['form_params'] = $requestData;
    }

    try {
      return $this->httpClient->request($method, $uri, $options);
    }
    catch (GuzzleException $e) {
      throw $e;
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

  /**
   * upload context to Smartling
   *
   * @param array $data
   * @return string
   */
  public function uploadContext($data) {
    return $this->sendRequest('context/html', $data, 'POST');
  }

  /**
   * Get statistics for context upload
   *
   * @param array $data
   * @return string
   */
  public function getContextStats($data) {
    return $this->sendRequest('context/html', $data, 'GET');
  }

  /**
   * Get list of authorized locales for given file.
   *
   * @param string $fileUri
   *   Value that uniquely identifies the file.
   * @param array $params
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   List of locales authorized in Smartling.
   *
   * @throws \Exception
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getAuthorizedLocales($fileUri, $params = []) {
    $params['fileUri'] = $fileUri;
    return $this->sendRequest('file/authorized_locales', $params, 'GET');
  }

}
