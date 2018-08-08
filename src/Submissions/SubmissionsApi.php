<?php

namespace Smartling\Submissions;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\Submissions\Params\SearchSubmissionsParams;

/**
 * Class SubmissionsApi
 * @package Smartling\Submissions
 */
class SubmissionsApi extends BaseApiAbstract
{
    const ENDPOINT_URL = 'https://api.smartling.com/submissions-api/v2/projects';

    /**
     * @param AuthApiInterface $authProvider
     * @param string $projectId
     * @param LoggerInterface $logger
     *
     * @return SubmissionsApi
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
     * @param array $params
     * @return mixed
     * @throws SmartlingApiException
     */
    public function createSubmission($bucketName, array $params)
    {
        $requestData = $this->getDefaultRequestData('json', $params);
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
     * @param string $submissionId
     * @param array $params
     * @return mixed
     * @throws SmartlingApiException
     */
    public function updateSubmission($bucketName, $submissionId, array $params)
    {
        $requestData = $this->getDefaultRequestData('json', $params);
        $requestUri = vsprintf('buckets/%s/submissions/%s', [$bucketName, $submissionId]);
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
        $result = $this->sendRequest($requestUri, $requestData, self::HTTP_METHOD_GET);
        return true === $result ? [] : $result;
    }
}