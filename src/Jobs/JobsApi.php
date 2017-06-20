<?php

namespace Smartling\Jobs;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Jobs\Params\CreateJobParameters;
use Smartling\Jobs\Params\ListJobsParameters;
use Smartling\Jobs\Params\SearchJobsParameters;

/**
 * Class JobsApi
 *
 * @package Smartling\Project
 */
class JobsApi extends BaseApiAbstract
{

    const ENDPOINT_URL = 'https://api.smartling.com/jobs-api/v2/projects';

    /**
     * @param AuthApiInterface $authProvider
     * @param string $projectId
     * @param LoggerInterface $logger
     *
     * @return JobsApi
     */
    public static function create(AuthApiInterface $authProvider, $projectId, $logger = null)
    {

        $client = self::initializeHttpClient(self::ENDPOINT_URL);

        $instance = new self($projectId, $client, $logger, self::ENDPOINT_URL);
        $instance->setAuth($authProvider);

        return $instance;
    }

    /**
     * @param CreateJobParameters $parameters
     * @return bool
     */
    public function createJob(CreateJobParameters $parameters)
    {
        return $this->sendRequest('jobs/', $parameters->exportToArray(), self::HTTP_METHOD_POST,
            self::STRATEGY_SEARCH);
    }

    /**
     * @param ListJobsParameters $parameters
     * @return bool
     */
    public function listJobs(ListJobsParameters $parameters)
    {
        return $this->sendRequest('jobs/', $parameters->exportToArray(), self::HTTP_METHOD_GET);
    }

    /**
     * @param string $jobId
     * @return bool
     */
    public function getJob($jobId)
    {
        return $this->sendRequest('jobs/' . $jobId, [], self::HTTP_METHOD_GET);
    }

    public function authorizeJob($jobId)
    {
        $endpoint = vsprintf('jobs/%s/authorize', [$jobId]);
        return $this->sendRequest($endpoint, [], self::HTTP_METHOD_POST, self::STRATEGY_NOBODY);
    }

    public function addFileToJob($jobId, $fileUri)
    {
        $endpoint = vsprintf('jobs/%s/file/add/', [$jobId]);
        return $this->sendRequest($endpoint, ['fileUri' => $fileUri], self::HTTP_METHOD_POST, self::STRATEGY_SEARCH);
    }

    /**
     * Search/Find Job(s), based on different query criteria passed in.
     *
     * @param \Smartling\Jobs\Params\SearchJobsParameters $parameters
     *
     * @return array
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function searchJobs(SearchJobsParameters $parameters)
    {
        return $this->sendRequest('jobs/search', $parameters->exportToArray(), self::HTTP_METHOD_POST,
            self::STRATEGY_SEARCH);
    }

}
