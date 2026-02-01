<?php

namespace Smartling\FileTranslations;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\FileTranslations\Params\TranslateFileParameters;
use Smartling\FileTranslations\Params\UploadFileParameters;

/**
 * Class FileTranslationsApi
 *
 * Provides machine translation capabilities for uploaded files via the Instant Translation feature.
 * Unlike other APIs that use project-scoped endpoints, this API operates at the account level.
 *
 * @package Smartling\FileTranslations
 */
class FileTranslationsApi extends BaseApiAbstract
{
    const ENDPOINT_URL = 'https://api.smartling.com/file-translations-api/v2/accounts';

    /**
     * Account UID for file translations operations.
     *
     * @var string
     */
    private $accountUid;

    /**
     * FileTranslationsApi constructor.
     *
     * @param string $accountUid
     *   Account UID in Smartling dashboard
     * @param \GuzzleHttp\ClientInterface $client
     *   HTTP client instance
     * @param LoggerInterface $logger
     *   Logger instance
     * @param string|null $service_url
     *   Service URL override
     */
    public function __construct($accountUid, $client, $logger = null, $service_url = null)
    {
        // Pass accountUid as projectId to satisfy parent constructor, but we'll override the base URL
        parent::__construct($accountUid, $client, $logger, $service_url);
        $this->accountUid = $accountUid;
    }

    /**
     * Factory method to create FileTranslationsApi instance.
     *
     * @param AuthApiInterface $authProvider
     *   Authentication provider
     * @param string $accountUid
     *   Account UID in Smartling dashboard
     * @param LoggerInterface $logger
     *   Logger instance
     *
     * @return FileTranslationsApi
     */
    public static function create(AuthApiInterface $authProvider, $accountUid, $logger = null)
    {
        $client = self::initializeHttpClient(self::ENDPOINT_URL);

        $instance = new self($accountUid, $client, $logger, self::ENDPOINT_URL);
        $instance->setAuth($authProvider);

        return $instance;
    }

    /**
     * {@inheritdoc}
     *
     * Overrides base implementation to handle file upload with request JSON part.
     */
    protected function processBodyOptions($requestData = [])
    {
        $opts = parent::processBodyOptions($requestData);

        if (!empty($opts['multipart'])) {
            foreach ($opts['multipart'] as &$data) {
                if ($data['name'] === 'file') {
                    $data['contents'] = $this->readFile($data['contents']);
                    if (array_key_exists('filename', $opts)) {
                        $data['filename'] = $opts['filename'];
                    }
                }
            }
        }

        return $opts;
    }

    /**
     * Get account UID.
     *
     * @return string
     */
    protected function getAccountUid()
    {
        return $this->accountUid;
    }

    /**
     * Uploads a file for machine translation.
     *
     * @param string $realPath
     *   Real path to the file to read in into stream.
     * @param string $fileName
     *   Logical filename for the file.
     * @param string $fileType
     *   File type identifier (json, xml, html, etc.)
     * @param UploadFileParameters $params
     *   Optional additional parameters
     *
     * @return array
     *   Response data containing fileUid
     *
     * @throws SmartlingApiException
     */
    public function uploadFile($realPath, $fileName, $fileType, UploadFileParameters $params = null)
    {
        if (is_null($params)) {
            $params = new UploadFileParameters();
        }

        // Build request JSON object
        $requestJson = [
            'fileType' => $fileType,
        ];

        // Merge any additional parameters
        $additionalParams = $params->exportToArray();
        if (!empty($additionalParams)) {
            $requestJson = array_merge($requestJson, $additionalParams);
        }

        // Build multipart request
        $multipartParams = [
            'file' => $realPath,
            'request' => json_encode($requestJson),
        ];

        $requestData = $this->getDefaultRequestData('multipart', $multipartParams);
        $requestData['filename'] = $fileName;

        return $this->sendRequest('files', $requestData, self::HTTP_METHOD_POST);
    }

    /**
     * Initiates machine translation for an uploaded file.
     *
     * @param string $fileUid
     *   File UID returned from uploadFile
     * @param TranslateFileParameters $params
     *   Translation parameters (source locale, target locales, callback URL)
     *
     * @return array
     *   Response data containing mtUid (machine translation UID)
     *
     * @throws SmartlingApiException
     */
    public function translateFile($fileUid, TranslateFileParameters $params)
    {
        $requestParams = $params->exportToArray();

        $requestData = $this->getDefaultRequestData('json', $requestParams);

        return $this->sendRequest("files/{$fileUid}/mt", $requestData, self::HTTP_METHOD_POST);
    }

    /**
     * Gets the translation progress/status for a machine translation job.
     *
     * @param string $fileUid
     *   File UID
     * @param string $mtUid
     *   Machine translation UID returned from translateFile
     *
     * @return array
     *   Response data containing status (IN_PROGRESS, COMPLETED, FAILED, CANCELLED)
     *
     * @throws SmartlingApiException
     */
    public function getTranslationProgress($fileUid, $mtUid)
    {
        $requestData = $this->getDefaultRequestData('query', []);

        return $this->sendRequest("files/{$fileUid}/mt/{$mtUid}/status", $requestData, self::HTTP_METHOD_GET);
    }

    /**
     * Downloads a translated file for a specific locale.
     *
     * @param string $fileUid
     *   File UID
     * @param string $mtUid
     *   Machine translation UID
     * @param string $localeId
     *   Target locale ID (e.g., 'es', 'fr', 'de')
     *
     * @return string
     *   Raw file content
     *
     * @throws SmartlingApiException
     */
    public function downloadTranslatedFile($fileUid, $mtUid, $localeId)
    {
        $requestData = $this->getDefaultRequestData('query', []);
        unset($requestData['headers']['Accept']);

        return $this->sendRequest("files/{$fileUid}/mt/{$mtUid}/locales/{$localeId}/file", $requestData, self::HTTP_METHOD_GET, true);
    }

    /**
     * Downloads all translations as a ZIP archive.
     *
     * @param string $fileUid
     *   File UID
     * @param string $mtUid
     *   Machine translation UID
     *
     * @return string
     *   Raw ZIP file content
     *
     * @throws SmartlingApiException
     */
    public function downloadAllTranslationsZip($fileUid, $mtUid)
    {
        $requestData = $this->getDefaultRequestData('query', []);
        unset($requestData['headers']['Accept']);

        return $this->sendRequest("files/{$fileUid}/mt/{$mtUid}/locales/all/file/zip", $requestData, self::HTTP_METHOD_GET, true);
    }

    /**
     * Cancels an in-progress machine translation job.
     *
     * @param string $fileUid
     *   File UID
     * @param string $mtUid
     *   Machine translation UID
     *
     * @return bool
     *   True on success
     *
     * @throws SmartlingApiException
     */
    public function cancelFileTranslation($fileUid, $mtUid)
    {
        $requestData = $this->getDefaultRequestData('json', []);

        return $this->sendRequest("files/{$fileUid}/mt/{$mtUid}/cancel", $requestData, self::HTTP_METHOD_POST);
    }
}
