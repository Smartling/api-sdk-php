<?php

namespace Smartling\Batch;

use GuzzleHttp\ClientInterface;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\ExpectedValues;
use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Batch\Params\ListBatchesParameters;
use Smartling\Exceptions\SmartlingApiException;

class BatchApiV2 extends BaseApiAbstract
{
    public const ENDPOINT_URL = 'https://api.smartling.com/jobs-batches-api/v2/projects';

    public function __construct(
        AuthApiInterface $authProvider,
        string $projectId,
        LoggerInterface $logger = null,
        ClientInterface $client = null
    ) {
        if ($client === null) {
            $client = self::initializeHttpClient(self::ENDPOINT_URL);
        }
        parent::__construct($projectId, $client, $logger, self::ENDPOINT_URL);
        $this->setAuth($authProvider);
    }

    /**
     * @throws SmartlingApiException
     */
    #[ArrayShape(['batchUid' => 'string'])]
    public function createBatch(bool $authorize,
        string $translationJobUid,
        array $fileUris,
        array $localeWorkflows = []
    ): array {
        if (count($fileUris) === 0) {
            throw new \UnexpectedValueException('FileUris cannot be empty.');
        }
        $parameters = [
            'authorize' => $authorize,
            'translationJobUid' => $translationJobUid,
            'fileUris' => $fileUris,
        ];
        if (count($localeWorkflows) !== 0) {
            $parameters['localeWorkflows'] = $localeWorkflows;
        }
        return $this->sendRequest(
            'batches',
            $this->getDefaultRequestData('json', $parameters),
            self::HTTP_METHOD_POST,
        );
    }

    /**
     * @throws SmartlingApiException
     */
    public function listBatches(ListBatchesParameters $parameters): array
    {
        return $this->sendRequest(
            "batches",
            $this->getDefaultRequestData('query', $parameters->exportToArray()),
            self::HTTP_METHOD_GET,
        );
    }

    /**
     * @throws SmartlingApiException
     */
    #[ArrayShape([
        'authorized' => 'bool',
        'files' => 'array',
        'generalErrors' => 'string',
        'projectId' => 'string',
        'status' => 'string',
        'translationJobUid' => 'string',
        'updatedDate' => 'string',
    ])]
    public function getBatchStatus(string $batchUid): array {
        $this->assertBatchUid($batchUid);
        return $this->sendRequest(
            "batches/$batchUid",
            $this->getDefaultRequestData('query', []),
            self::HTTP_METHOD_GET,
        );
    }

    /**
     * @throws SmartlingApiException
     */
    public function processBatchAction(
        string $batchUid,
        #[ExpectedValues(['CANCEL_FILE', 'REGISTER_FILE'])] string $action,
        string $fileUri,
        string $reason = null
    ): void {
        $this->assertBatchUid($batchUid);
        $parameters = [
            'action' => $action,
            'fileUri' => $fileUri,
        ];
        if ($reason !== null) {
            $parameters['reason'] = $reason;
        }
        $this->sendRequest(
            "batches/$batchUid",
            $this->getDefaultRequestData('json', $parameters),
            self::HTTP_METHOD_PUT,
        );
    }

    public function uploadFileToABatch(string $batchUid, string $file, string $fileUri, string $fileType, array $localeIdsToAuthorize, string $smartlingNamespace = null, string $smartlingFileCharset = null, string $callbackUrl = null): void {
        $this->assertBatchUid($batchUid);

        $parameters = [
            'file' => $file,
            'fileUri' => $fileUri,
            'fileType' => $fileType,
            'localeIdsToAuthorize' => $localeIdsToAuthorize,
        ];
        if ($smartlingNamespace !== null) {
            $parameters['smartling.namespace'] = $smartlingNamespace;
        }
        if ($smartlingFileCharset !== null) {
            $parameters['smartling.file_charset'] = $smartlingFileCharset;
        }
        if ($callbackUrl !== null) {
            $parameters['callbackUrl'] = $callbackUrl;
        }

        $requestData = $this->getDefaultRequestData('multipart', $parameters);

        $this->sendRequest("batches/$batchUid/file", $requestData, self::HTTP_METHOD_POST);
    }

    private function assertBatchUid(string $batchUid)
    {
        if ($batchUid === '') {
            throw new \UnexpectedValueException('BatchUid cannot be empty.');
        }
    }
}
