<?php

namespace Smartling\File;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\File\Params\DownloadFileParameters;
use Smartling\File\Params\ListFilesParameters;
use Smartling\File\Params\ExtendedListFilesParameters;
use Smartling\Parameters\ParameterInterface;
use Smartling\File\Params\UploadFileParameters;


/**
 * Class FileApi
 *
 * @package Smartling\Api
 */
class FileApi extends BaseApiAbstract
{

    const ENDPOINT_URL = 'https://api.smartling.com/files-api/v2/projects';

    /**
     * @param AuthApiInterface $authProvider
     * @param string $projectId
     * @param LoggerInterface $logger
     *
     * @return FileApi
     */
    public static function create(AuthApiInterface $authProvider, $projectId, $logger = null)
    {

        $client = self::initializeHttpClient(self::ENDPOINT_URL);

        $instance = new self($projectId, $client, $logger, self::ENDPOINT_URL);
        $instance->setAuth($authProvider);

        return $instance;
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
     * @param UploadFileParameters $params
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
     * @throws \Smartling\Exceptions\SmartlingApiException
     *
     * @see http://docs.smartling.com/pages/API/FileAPI/Upload-File/
     */
    public function uploadFile($realPath, $file_name, $file_type, UploadFileParameters $params = null)
    {
        if (is_null($params)) {
            $params = new UploadFileParameters();
        }
        $params = $params->exportToArray();

        $params['file'] = $realPath;
        $params['fileUri'] = $file_name;
        $params['fileType'] = $file_type;

        return $this->sendRequest('file', $params, self::HTTP_METHOD_POST, self::STRATEGY_UPLOAD);
    }

    /**
     * Requests last-modified value for all locales for file
     * @param string $fileUri
     *   Value that uniquely identifies the uploaded file. This ID can be used to
     *   request the file back.
     * @return array
     *   Data about uploaded file.
     *
     * @throws \Smartling\Exceptions\SmartlingApiException
     *
     * @see http://docs.smartling.com/pages/API/v2/FileAPI/Last-Modified/All-Locales/
     */
    public function lastModified($fileUri)
    {
        $params['fileUri'] = $fileUri;

        $result = $this->sendRequest('file/last-modified', $params, self::HTTP_METHOD_GET);

        /** @noinspection OffsetOperationsInspection */
        if (is_array($result) && array_key_exists('items', $result) && is_array($result['items']))
        {
            /** @noinspection OffsetOperationsInspection */
            foreach ($result['items'] as &$item)
            {

                $date = \DateTime::createFromFormat(self::PATTERN_DATE_TIME_ISO_8601, $item['lastModified']);

                if (!($date instanceof \DateTime))
                {
                    $date = \DateTime::createFromFormat(self::PATTERN_DATE_TIME_ISO_8601, '1970-01-01T00:00:00Z');
                }

                $item['lastModified'] = $date;
            }
        }

        return $result;
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
     * @param DownloadFileParameters $params
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
     * @throws \Smartling\Exceptions\SmartlingApiException
     *
     * @see http://docs.smartling.com/pages/API/FileAPI/Download-File/
     */
    public function downloadFile($fileUri, $locale = '', DownloadFileParameters $params = null)
    {
        if ((!is_string($locale)) || strlen($locale) < 2) {
            $message = vsprintf(
                'Invalid locale value got. Expected a string of at least 2 chars length, but got: %s',
                [
                    '' === $locale ? 'Empty string' : var_export($locale, true)
                ]
            );

            throw new SmartlingApiException($message);
        }

        $params = (is_null($params)) ? [] : $params->exportToArray();
        $params['fileUri'] = $fileUri;

        return $this->sendRequest("locales/{$locale}/file", $params, self::HTTP_METHOD_GET, self::STRATEGY_DOWNLOAD);
    }

    /**
     * Retrieves status about file translation progress.
     *
     * @param string $fileUri
     *   Value that uniquely identifies the file.
     * @param string $locale
     *   A locale identifier as specified in project setup.
     * @param ParameterInterface $params
     *   Additional parameters that might be added later
     *
     * @return array Data about request file.
     * Data about request file.
     * @throws SmartlingApiException
     * @see http://docs.smartling.com/pages/API/FileAPI/Status/
     */
    public function getStatus($fileUri, $locale, ParameterInterface $params = null)
    {
        $params = (is_null($params)) ? [] : $params->exportToArray();
        $params['fileUri'] = $fileUri;

        return $this->sendRequest("locales/$locale/file/status", $params, self::HTTP_METHOD_GET);
    }

    /**
     * Retrieves status about file translation progress for all locales.
     *
     * @param string $fileUri
     *   Value that uniquely identifies the file.
     * @param ParameterInterface $params
     *   Additional parameters that might be added later
     *
     * @return array Data about request file.
     * Data about request file.
     * @throws SmartlingApiException
     * @see http://docs.smartling.com/pages/API/v2/FileAPI/Status/All-Locales/
     */
    public function getStatusForAllLocales($fileUri, ParameterInterface $params = null)
    {
        $params = (is_null($params)) ? [] : $params->exportToArray();
        $params['fileUri'] = $fileUri;

        return $this->sendRequest("/file/status", $params, self::HTTP_METHOD_GET);
    }

    /**
     * Lists recently uploaded files. Returns a maximum of 500 files.
     *
     * @param ListFilesParameters $params
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
     *
     * @return array
     *   List of files objects.
     *
     * @throws \Smartling\Exceptions\SmartlingApiException
     *
     * @see http://docs.smartling.com/pages/API/FileAPI/List/
     */
    public function getList(ListFilesParameters $params = null)
    {
        $params = (is_null($params)) ? [] : $params->exportToArray();

        return $this->sendRequest('files/list', $params, self::HTTP_METHOD_GET);
    }

    /**
     * @param string $locale
     * @param ExtendedListFilesParameters|null $params
     *   same as ListFilesParameters, but with a new property 'status' which for now
     *   can only be of a value 'COMPLETED'
     * @return bool
     */
    public function getExtendedList($locale, ExtendedListFilesParameters $params = null)
    {
        $params = (is_null($params)) ? [] : $params->exportToArray();

        return $this->sendRequest("locales/$locale/files/list", $params, self::HTTP_METHOD_GET);
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
     * @param ParameterInterface $params
     *
     * @return string Just empty string if everything was successfully.
     * Just empty string if everything was successfully.
     * @throws SmartlingApiException
     * @see http://docs.smartling.com/pages/API/FileAPI/Rename/
     */
    public function renameFile($fileUri, $newFileUri, ParameterInterface $params = null)
    {
        $params = (is_null($params)) ? [] : $params->exportToArray();
        $params['fileUri'] = $fileUri;
        $params['newFileUri'] = $newFileUri;

        return $this->sendRequest('file/rename', $params, self::HTTP_METHOD_POST);
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
     * @param ParameterInterface $params
     *
     * @return array
     * @throws SmartlingApiException
     */
    public function deleteFile($fileUri, ParameterInterface $params = null)
    {
        $params = (is_null($params)) ? [] : $params->exportToArray();

        $params['fileUri'] = $fileUri;

        return $this->sendRequest('file/delete', $params, self::HTTP_METHOD_POST);
    }

    /**
     * Import files form Service.
     *
     * @param string $locale
     *   The Smartling locale identifier for the language Smartling is importing.
     * @param string $fileUri
     *   The Smartling URI for file that contains the original language strings
     *   already uploaded to Smartling.
     * @param string $fileType
     *   The type of file used for imports. Valid values are: ios, android,
     *   gettext, javaProperties, xml, json, yaml, and csv.
     * @param string $fileRealPath
     *   Absolute path to the file on your local machine that contains the
     *   translated content,
     * @param string $translationState
     *   Value indicating the workflow state to import the translations into.
     *   Content will be imported into the language's default workflow.
     *   Could be 'PUBLISHED' or 'POST_TRANSLATION'.
     * @param boolean $overwrite
     *   (optional) An associative array of additional options, with the following
     *   elements:
     *   - 'overwrite': Boolean indicating whether or not to overwrite existing
     *     translations.
     *
     * @return string
     *
     * @throws \Smartling\Exceptions\SmartlingApiException
     *
     * @see http://docs.smartling.com/pages/API/Translation-Imports/
     */
    public function import($locale, $fileUri, $fileType, $fileRealPath, $translationState, $overwrite = false)
    {
        $params['fileUri'] = $fileUri;
        $params['fileType'] = $fileType;
        $params['file'] = $fileRealPath;
        $params['translationState'] = $translationState;
        $params['overwrite'] = $overwrite;

        return $this->sendRequest("/locales/$locale/file/import", $params, self::HTTP_METHOD_POST);
    }

    /**
     * Get list of authorized locales for given file.
     *
     * @param string $fileUri
     * @param ParameterInterface $params
     *
     * @return array
     * @throws SmartlingApiException
     */
    public function getAuthorizedLocales($fileUri, ParameterInterface $params = null)
    {
        $params = (is_null($params)) ? [] : $params->exportToArray();

        $params['fileUri'] = $fileUri;

        return $this->sendRequest('file/authorized-locales', $params, self::HTTP_METHOD_GET);
    }

    /**
     * retrieve all statuses about file translations progress
     *
     * @param                    $fileUri
     * @param ParameterInterface $params
     *
     * @return array
     * @throws SmartlingApiException
     */
    public function getStatusAllLocales($fileUri, ParameterInterface $params = null)
    {
        $params = (is_null($params)) ? [] : $params->exportToArray();

        $params['fileUri'] = $fileUri;

        return $this->sendRequest('file/status', $params, self::HTTP_METHOD_GET);
    }


    /**
     * retrieve all statuses about file translations progress
     *
     * @param string $fileUri
     * @param ParameterInterface $params
     *
     * @return array
     * @throws SmartlingApiException
     */
    public function getLastModified($fileUri, ParameterInterface $params = null)
    {
        $params = (is_null($params)) ? [] : $params->exportToArray();

        $params['fileUri'] = $fileUri;

        return $this->sendRequest('file/last-modified', $params, self::HTTP_METHOD_GET);
    }
}