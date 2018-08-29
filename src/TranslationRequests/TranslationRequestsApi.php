<?php

namespace Smartling\TranslationRequests;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\TranslationRequests\Params\CreateSubmissionParams;
use Smartling\TranslationRequests\Params\SearchSubmissionsParams;
use Smartling\TranslationRequests\Params\UpdateSubmissionParams;

/**
 * Class TranslationRequestsApi
 * @package Smartling\TranslationRequests
 */
class TranslationRequestsApi extends BaseApiAbstract
{
    const ENDPOINT_URL = 'https://api.smartling.com/submission-service-api/v2/projects';

    /**
     * @param AuthApiInterface $authProvider
     * @param string $projectId
     * @param LoggerInterface $logger
     *
     * @return TranslationRequestsApi
     */
    public static function create(AuthApiInterface $authProvider, $projectId, $logger = null)
    {
        $client = self::initializeHttpClient(self::ENDPOINT_URL);

        $instance = new self($projectId, $client, $logger, self::ENDPOINT_URL);
        $instance->setAuth($authProvider);

        return $instance;
    }

    /**
     * @param string $bucketName
     * @param CreateSubmissionParams $params
     * @return mixed
     * @throws SmartlingApiException
     */
    public function createSubmission($bucketName, CreateSubmissionParams $params)
    {
        $requestData = $this->getDefaultRequestData('json', $params->exportToArray());
        $requestUri = vsprintf('buckets/%s/submissions', [$bucketName]);
        return $this->sendRequest($requestUri, $requestData, self::HTTP_METHOD_POST);
    }


    /**
     * @param string $bucketName
     * @param string $submissionUid
     * @return array
     * @throws SmartlingApiException
     */
    public function getSubmission($bucketName, $submissionUid)
    {
        $requestData = $this->getDefaultRequestData('query', []);
        $requestUri = vsprintf('buckets/%s/submissions/%s', [$bucketName, $submissionUid]);
        return $this->sendRequest($requestUri, $requestData, self::HTTP_METHOD_GET);
    }

    /**
     * @param string $bucketName
     * @param string $submissionUid
     * @param UpdateSubmissionParams $params
     * @return mixed
     * @throws SmartlingApiException
     */
    public function updateSubmission($bucketName, $submissionUid, UpdateSubmissionParams $params)
    {
        $requestData = $this->getDefaultRequestData('json', $params->exportToArray());
        $requestUri = vsprintf('buckets/%s/submissions/%s', [$bucketName, $submissionUid]);
        return $this->sendRequest($requestUri, $requestData, self::HTTP_METHOD_PUT);
    }

    /**
     * @param string $bucketName
     * @param SearchSubmissionsParams $searchParams
     * @return array
     * @throws SmartlingApiException
     */
    public function searchSubmissions($bucketName, SearchSubmissionsParams $searchParams)
    {
        $requestData = $this->getDefaultRequestData('query', $searchParams->exportToArray());
        $requestUri = vsprintf('buckets/%s/submissions', [$bucketName]);
        return $this->sendRequest($requestUri, $requestData, self::HTTP_METHOD_GET);
    }
}