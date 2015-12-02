<?php

namespace Smartling;

use GuzzleHttp\ClientInterface;

class SmartlingApi
{

    const DEFAULT_SERVICE_URL = 'https://api.smartling.com/v1';

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

    /**
     * Creates SmartlingApi instance.
     *
     * @param string $apiKey
     *   Api Key string.
     * @param string $projectId
     *   Project ID string.
     * @param \GuzzleHttp\ClientInterface $http_client
     *   Instance of Guzzle http client.
     * @param string $base_service_url
     *   Url for Smartling translation service.
     */
    public function __construct($apiKey, $projectId, ClientInterface $http_client, $base_service_url = null)
    {
        $this->apiKey = $apiKey;
        $this->projectId = $projectId;
        $this->httpClient = $http_client;
        $this->baseUrl = rtrim($base_service_url ?: self::DEFAULT_SERVICE_URL , '/');
    }

    /**
     * Sends request to Smartling Service via Guzzle Client.
     *
     * @param string $uri
     *   Resource uri.
     * @param array $requestData
     *   Parameters to be send as query or multipart form elements.
     * @param string $method
     *   Http method uppercased.
     *
     * @return array
     *   Decoded JSON answer.
     *
     * @throws \Smartling\SmartlingApiException
     */
    protected function sendRequest($uri, $requestData, $method)
    {
        // Set api key and product id as required arguments.
        $requestData['apiKey'] = $this->apiKey;
        $requestData['projectId'] = $this->projectId;

        // Ask for JSON and disable Guzzle exceptions.
        $options = [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'http_errors' => FALSE,
        ];

        // For GET and DELETE http methods use just query parameter builder.
        if (in_array($method, ['GET', 'DELETE'])) {
            $options['query'] = $requestData;
        } else {
            $options['multipart'] = [];
            // Remove file from params array and add it as a stream.
            if (!empty($requestData['file'])) {
                $options['multipart'][] = [
                    'name' => 'file',
                    'contents' => $this->readFile($requestData['file']),
                ];
                unset($requestData['file']);
            }
            foreach ($requestData as $key => $value) {
                // Hack to cast FALSE to '0' instead of empty string.
                if (is_bool($value)) {
                    $value = (int)$value;
                }
                $options['multipart'][] = [
                    'name' => $key,
                    // Typecast everything to string to avoid curl notices.
                    'contents' => (string) $value,
                ];
            }
        }

        // Avoid double slashes in final URL.
        $uri = ltrim($uri, "/");

        $guzzle_response = $this->httpClient->request($method, $this->baseUrl . $uri, $options);
        $response_body = (string) $guzzle_response->getBody();

        // Catch all errors from Smartling and throw appropriate exception.
        if ($guzzle_response->getStatusCode() >= 400) {
            $error_response = json_decode($response_body, TRUE);

            if (!$error_response || empty($error_response['response']['messages'])) {
                throw new SmartlingApiException('Bad response format from Smartling');
            }

            throw new SmartlingApiException(implode(' || ', $error_response['response']['messages']), $guzzle_response->getStatusCode());
        }

        // "Download file" method return translated file directly.
        if ('file/get' === $uri) {
            return $response_body;
        }

        $response = json_decode($response_body, TRUE);
        // Throw exception if json is not valid.
        if (!$response || empty($response['response']['data'])) {
            throw new SmartlingApiException('Bad response format from Smartling');
        }

        return $response['response']['data'];
    }

    /**
     * Get locale list for project.
     *
     * @return array
     *   List of locales objects.
     *
     * @throws \Smartling\SmartlingApiException
     *
     * @see http://docs.smartling.com/pages/API/Projects/
     */
    public function getLocaleList()
    {
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
     * @return array
     *   Data about uploaded file.
     *
     * @throws \Smartling\SmartlingApiException
     *
     * @see http://docs.smartling.com/pages/API/FileAPI/Upload-File/
     */
    public function uploadFile($realPath, $file_name, $file_type, $params = array())
    {
        $params['file'] = $realPath;
        $params['fileUri'] = $file_name;
        $params['fileType'] = $file_type;
        return $this->sendRequest('file/upload', $params, 'POST');
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
     * @return string
     *   File content.
     *
     * @throws \Smartling\SmartlingApiException
     *
     * @see http://docs.smartling.com/pages/API/FileAPI/Download-File/
     */
    public function downloadFile($fileUri, $locale = '', $params = [])
    {
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
     * @return array
     *   Data about request file.
     *
     * @throws \Smartling\SmartlingApiException
     *
     * @see http://docs.smartling.com/pages/API/FileAPI/Status/
     */
    public function getStatus($fileUri, $locale, $params = [])
    {
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
     * @return array
     *   List of files objects.
     *
     * @throws \Smartling\SmartlingApiException
     *
     * @see http://docs.smartling.com/pages/API/FileAPI/List/
     */
    public function getList($locale = '', $params = [])
    {
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
     * @return string
     *   Just empty string if everything was successfully.
     *
     * @throws \Smartling\SmartlingApiException
     *
     * @see http://docs.smartling.com/pages/API/FileAPI/Rename/
     */
    public function renameFile($fileUri, $newFileUri, $params = [])
    {
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
     *
     * @throws \Smartling\SmartlingApiException
     */
    public function deleteFile($fileUri)
    {
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
     * @throws \Smartling\SmartlingApiException
     *
     * @see http://docs.smartling.com/pages/API/Translation-Imports/
     */
    public function import($fileUri, $fileType, $locale, $fileRealPath, $translationState, $params = [])
    {
        $params['fileUri'] = $fileUri;
        $params['fileType'] = $fileType;
        $params['locale'] = $locale;
        $params['file'] = $fileRealPath;
        $params['translationState'] = $translationState;
        return $this->sendRequest('file/import', $params, 'POST');
    }

    /**
     * upload context to Smartling
     *
     * @param array $data
     * @return string
     */
    public function uploadContext($data)
    {
        return $this->sendRequest('context/html', $data, 'POST');
    }

    /**
     * Get statistics for context upload
     *
     * @param array $data
     * @return string
     */
    public function getContextStats($data)
    {
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
     * @throws \Smartling\SmartlingApiException
     */
    public function getAuthorizedLocales($fileUri, $params = [])
    {
        $params['fileUri'] = $fileUri;
        return $this->sendRequest('file/authorized_locales', $params, 'GET');
    }

    /**
     * OOP wrapper for fopen() function.
     *
     * @param string $realPath
     *   Real path for file.
     *
     * @return resource
     *
     * @throws \Smartling\SmartlingApiException
     */
    protected function readFile($realPath) {
        $stream = @fopen($realPath, 'r');

        if (!$stream) {
            throw new SmartlingApiException("File $realPath was not able to be read.");
        }
        else {
            return $stream;
        }
    }

}
