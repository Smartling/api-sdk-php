<?php

namespace Smartling\ProgressTracker;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\ProgressTracker\Params\RecordParameters;

/**
 * Class ProgressTrackerApi
 *
 * @package Smartling\ProgressTracker
 */
class ProgressTrackerApi extends BaseApiAbstract
{
    const ENDPOINT_URL = 'https://api.smartling.com/progress-tracker-api/v2';

    /**
     * {@inheritdoc}
     */
    public function __construct($projectId, ClientInterface $client, $logger = null, $service_url = null)
    {
        parent::__construct($projectId, $client, $logger, $service_url);

        // Do not include project_id into base url since
        // progress tracker service has /accounts/{account}/token
        // endpoint without project id.
        $this->setBaseUrl(rtrim($service_url, '/'));
    }

    /**
     * @param AuthApiInterface $authProvider
     * @param string $projectId
     * @param LoggerInterface $logger
     *
     * @return ProgressTrackerApi
     */
    public static function create(AuthApiInterface $authProvider, $projectId, $logger = null)
    {
        $client = static::initializeHttpClient(self::ENDPOINT_URL);

        $instance = new self($projectId, $client, $logger, self::ENDPOINT_URL);
        $instance->setAuth($authProvider);

        return $instance;
    }

    /**
     * @param string $accountUid
     *
     * @return array
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function getToken($accountUid)
    {
        $requestData = $this->getDefaultRequestData('query', []);
        $endpoint = vsprintf('accounts/%s/token', [$accountUid]);

        return $this->sendRequest($endpoint, $requestData, static::HTTP_METHOD_GET);
    }

    /**
     * Creates a new record.
     *
     * @param $spaceId
     * @param $objectId
     * @param RecordParameters $parameters
     *
     * @return array
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function createRecord($spaceId, $objectId, RecordParameters $parameters)
    {
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());
        $endpoint = vsprintf('projects/%s/spaces/%s/objects/%s/records', [
            $this->getProjectId(),
            $spaceId,
            $objectId,
        ]);

        return $this->sendRequest($endpoint, $requestData, static::HTTP_METHOD_POST);
    }

    /**
     * Deletes record.
     *
     * @param $spaceId
     * @param $objectId
     * @param $recordId
     *
     * @return array
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function deleteRecord($spaceId, $objectId, $recordId)
    {
        $requestData = $this->getDefaultRequestData('query', []);
        $endpoint = vsprintf('projects/%s/spaces/%s/objects/%s/records/%s', [
            $this->getProjectId(),
            $spaceId,
            $objectId,
            $recordId
        ]);

        return $this->sendRequest($endpoint, $requestData, static::HTTP_METHOD_DELETE);
    }

    /**
     * Updates record
     * @param $spaceId
     * @param $objectId
     * @param $recordId
     * @param RecordParameters $parameters
     * @return mixed
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function updateRecord($spaceId, $objectId, $recordId, RecordParameters $parameters)
    {
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());
        $endpoint = vsprintf('projects/%s/spaces/%s/objects/%s/records/%s', [
            $this->getProjectId(),
            $spaceId,
            $objectId,
            $recordId
        ]);

        return $this->sendRequest($endpoint, $requestData, static::HTTP_METHOD_PUT);
    }
}
