<?php

namespace Smartling\AuditLog;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Smartling\AuditLog\Params\CreateRecordRecommendedParameters;
use Smartling\AuditLog\Params\SearchRecordParameters;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;

class AuditLogApi extends BaseApiAbstract
{

    const ENDPOINT_URL = 'https://api.smartling.com/audit-log-api/v2';

    /**
     * {@inheritdoc}
     */
    public function __construct($projectId, ClientInterface $client, LoggerInterface $logger = null, $service_url = null)
    {
        parent::__construct($projectId, $client, $logger, $service_url);

        // Do not include project_id into base url since
        // progress tracker service has /accounts/{account}/token
        // endpoint without project id.
        $this->setBaseUrl(rtrim($service_url, '/'));
    }

    public static function create(AuthApiInterface $authProvider, $projectId, LoggerInterface $logger = null)
    {

        $client = self::initializeHttpClient(self::ENDPOINT_URL);

        $instance = new self($projectId, $client, $logger, self::ENDPOINT_URL);
        $instance->setAuth($authProvider);

        return $instance;
    }

    public function createProjectLevelLogRecord(CreateRecordRecommendedParameters $createRecordRecommendedParameters) {
        $requestData = $this->getDefaultRequestData('json', $createRecordRecommendedParameters->exportToArray());
        $endpoint = vsprintf('projects/%s/logs', [
            $this->getProjectId(),
        ]);

        return $this->sendRequest($endpoint, $requestData, static::HTTP_METHOD_POST);
    }

    public function createAccountLevelLogRecord($accountUid, CreateRecordRecommendedParameters $createRecordRecommendedParameters) {
        $requestData = $this->getDefaultRequestData('json', $createRecordRecommendedParameters->exportToArray());
        $endpoint = vsprintf('accounts/%s/logs', [
            $accountUid,
        ]);

        return $this->sendRequest($endpoint, $requestData, static::HTTP_METHOD_POST);
    }

    public function searchProjectLevelLogRecord(SearchRecordParameters $searchParameters) {
        $requestData = $this->getDefaultRequestData('query', $searchParameters->exportToArray());
        $endpoint = vsprintf('projects/%s/logs', [
            $this->getProjectId(),
        ]);

        return $this->sendRequest($endpoint, $requestData, static::HTTP_METHOD_GET);
    }

    public function searchAccountLevelLogRecord($accountUid, SearchRecordParameters $searchParameters) {
        $requestData = $this->getDefaultRequestData('query', $searchParameters->exportToArray());
        $endpoint = vsprintf('accounts/%s/logs', [
            $accountUid,
        ]);

        return $this->sendRequest($endpoint, $requestData, static::HTTP_METHOD_GET);
    }

}
