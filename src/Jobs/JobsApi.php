<?php

namespace Smartling\Jobs;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Jobs\Params\AddFileToJobParameters;
use Smartling\Jobs\Params\CancelJobParameters;
use Smartling\Jobs\Params\CreateJobParameters;
use Smartling\Jobs\Params\ListJobsParameters;
use Smartling\Jobs\Params\SearchJobsParameters;
use Smartling\Jobs\Params\UpdateJobParameters;

/**
 * Class JobsApi
 *
 * @package Smartling\Project
 */
class JobsApi extends BaseApiAbstract
{

    const ENDPOINT_URL = 'https://api.smartling.com/jobs-api/v2/projects';

    /**
     * Instantiates Jobs API object.
     *
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
     * Creates a job.
     *
     * @param CreateJobParameters $parameters
     * @return array
     */
    public function createJob(CreateJobParameters $parameters)
    {
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());

        return $this->sendRequest('jobs', $requestData, self::HTTP_METHOD_POST);
    }

    /**
    * Updates a job.
    *
    * @param string $jobId
    * @param UpdateJobParameters $parameters
    * @return array
    */
    public function updateJob($jobId, UpdateJobParameters $parameters)
    {
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());

        return $this->sendRequest('jobs/' . $jobId, $requestData, self::HTTP_METHOD_PUT);
    }

    /**
     * Cancels a job.
     *
     * @param string $jobId
     * @param CancelJobParameters $parameters
     * @return bool
     */
    public function cancelJob($jobId, CancelJobParameters $parameters)
    {
        $endpoint = vsprintf('jobs/%s/cancel', [$jobId]);
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());

        return $this->sendRequest($endpoint, $requestData, self::HTTP_METHOD_POST);
    }

    /**
     * Returns a list of jobs.
     *
     * @param ListJobsParameters $parameters
     * @return array
     */
    public function listJobs(ListJobsParameters $parameters)
    {
        $requestData = $this->getDefaultRequestData('query', $parameters->exportToArray());

        return $this->sendRequest('jobs', $requestData, self::HTTP_METHOD_GET);
    }

    /**
     * Returns a job.
     *
     * @param string $jobId
     * @return array
     */
    public function getJob($jobId)
    {
        $requestData = $this->getDefaultRequestData('query', []);

        return $this->sendRequest('jobs/' . $jobId, $requestData, self::HTTP_METHOD_GET);
    }

    /**
     * Authorizes a job.
     *
     * @param $jobId
     * @return bool
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function authorizeJob($jobId)
    {
        $endpoint = vsprintf('jobs/%s/authorize', [$jobId]);
        $requestData = $this->getDefaultRequestData('form_params', []);
        $requestData['headers']['Content-Type'] = 'application/json';

        return $this->sendRequest($endpoint, $requestData, self::HTTP_METHOD_POST);
    }

    /**
     * Adds file to a job.
     *
     * @param $jobId
     * @param \Smartling\Jobs\Params\AddFileToJobParameters $parameters
     * @return array
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function addFileToJob($jobId, AddFileToJobParameters $parameters)
    {
        $endpoint = vsprintf('jobs/%s/file/add', [$jobId]);
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());

        return $this->sendRequest($endpoint, $requestData, self::HTTP_METHOD_POST);
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
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());

        return $this->sendRequest('jobs/search', $requestData, self::HTTP_METHOD_POST);
    }

}
