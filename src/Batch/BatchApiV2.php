<?php

namespace Smartling\Batch;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\Polyfills;

class BatchApiV2 extends BaseApiAbstract
{
    public const ENDPOINT_URL = 'https://api.smartling.com/job-batches-api/v2/projects';

    private const ACTION_CANCEL_FILE = 'CANCEL_FILE';

    public function __construct(
        AuthApiInterface $authProvider,
        string $projectId,
        LoggerInterface $logger = null,
        ClientInterface $client = null
    )
    {
        if ($client === null) {
            $client = self::initializeHttpClient(self::ENDPOINT_URL);
        }
        parent::__construct($projectId, $client, $logger, self::ENDPOINT_URL);
        $this->setAuth($authProvider);
    }

    /**
     * @throws SmartlingApiException
     */
    public function cancelBatchFile(string $batchUid, string $fileUri, string $reason = null): void
    {
        if ($batchUid === '') {
            throw new \UnexpectedValueException('BatchUid cannot be empty.');
        }
        $parameters = [
            'action' => self::ACTION_CANCEL_FILE,
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

    /**
     * @throws SmartlingApiException
     */
    public function createBatch(
        bool $authorize,
        string $translationJobUid,
        array $fileUris,
        array $localeWorkflows = []
    ): string
    {
        if (count($fileUris) === 0) {
            throw new \UnexpectedValueException('FileUris cannot be empty.');
        }
        if (!function_exists('array_is_list')) {
            function array_is_list(array $array): bool
            {
                return Polyfills::array_is_list($array);
            }
        }
        if (!array_is_list($fileUris)) {
            throw new \UnexpectedValueException('FileUris must be a list.');
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
        )['batchUid'];
    }
}
