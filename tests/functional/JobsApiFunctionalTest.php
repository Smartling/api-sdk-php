<?php

namespace Smartling\Tests\Functional;

use DateTime;
use DateTimeZone;
use PHPUnit_Framework_TestCase;
use Smartling\AuthApi\AuthTokenProvider;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\File\FileApi;
use Smartling\Jobs\JobsApi;
use Smartling\Jobs\Params\AddFileToJobParameters;
use Smartling\Jobs\Params\AddLocaleToJobParameters;
use Smartling\Jobs\Params\CancelJobParameters;
use Smartling\Jobs\Params\CreateJobParameters;
use Smartling\Jobs\Params\ListJobsParameters;
use Smartling\Jobs\Params\SearchJobsParameters;
use Smartling\Jobs\Params\UpdateJobParameters;

/**
 * Test class for Jobs API examples.
 */
class JobsApiFunctionalTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var JobsApi
     */
    private $jobsApi;

    /**
     * @var FileApi
     */
    private $fileApi;

    /**
     * @var string $jobId
     */
    private $jobId = false;

    /**
     * Test mixture.
     */
    public function setUp()
    {
        $projectId = getenv('project_id');
        $userIdentifier = getenv('user_id');
        $userSecretKey = getenv('user_key');

        if (
            empty($projectId) ||
            empty($userIdentifier) ||
            empty($userSecretKey)
        ) {
            $this->fail('Missing required parameters');
        }

        $authProvider = AuthTokenProvider::create($userIdentifier, $userSecretKey);
        $this->jobsApi = JobsApi::create($authProvider, $projectId);
        $this->fileApi = FileApi::create($authProvider, $projectId);

        try {
            $this->jobId = $this->createJob();
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    public function tearDown()
    {
        try {
            $this->cancelJob($this->jobId);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Create job.
     *
     * @return string
     */
    private function createJob()
    {
        try {
            $params = new CreateJobParameters();
            $params->setName('Test Job Name ' . time());
            $params->setDescription('Test Job Description ' . time());
            $params->setDueDate(DateTime::createFromFormat('Y-m-d H:i:s', '2020-01-01 19:19:17',
                new DateTimeZone('UTC')));
            $params->setTargetLocales(['es', 'fr']);
            $result = $this->jobsApi->createJob($params);

            $this->assertArrayHasKey('translationJobUid', $result);
            $this->assertArrayHasKey('jobName', $result);
            $this->assertArrayHasKey('targetLocaleIds', $result);
            $this->assertArrayHasKey('description', $result);
            $this->assertArrayHasKey('dueDate', $result);
            $this->assertArrayHasKey('referenceNumber', $result);
            $this->assertArrayHasKey('callbackUrl', $result);
            $this->assertArrayHasKey('callbackMethod', $result);
            $this->assertArrayHasKey('createdDate', $result);
            $this->assertArrayHasKey('modifiedDate', $result);
            $this->assertArrayHasKey('createdByUserUid', $result);
            $this->assertArrayHasKey('modifiedByUserUid', $result);
            $this->assertArrayHasKey('firstCompletedDate', $result);
            $this->assertArrayHasKey('lastCompletedDate', $result);
            $this->assertArrayHasKey('jobStatus', $result);

            $result = $result['translationJobUid'];
        } catch (SmartlingApiException $e) {
            $result = false;
        }

        return $result;
    }

    /**
     * Cancel job.
     *
     * @param string $jobId
     */
    private function cancelJob($jobId)
    {
        $params = new CancelJobParameters();
        $params->setReason('Some reason to cancel');
        $this->jobsApi->cancelJobSync($jobId, $params);
    }

    /**
     * Test for job list.
     */
    public function testJobsApiListJobs()
    {
        try {
            $result = $this->jobsApi->listJobs(new ListJobsParameters());

            $this->assertArrayHasKey('totalCount', $result);
            $this->assertArrayHasKey('items', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for job update.
     */
    public function testJobsApiUpdateJob()
    {
        try {
            $params = new UpdateJobParameters();
            $params->setName("Test Job Name Updated " . time());
            $params->setDescription("Test Job Description Updated " . time());
            $params->setDueDate(DateTime::createFromFormat('Y-m-d H:i:s', '2030-01-01 19:19:17',
                new DateTimeZone('UTC')));
            $result = $this->jobsApi->updateJob($this->jobId, $params);

            $this->assertArrayHasKey('translationJobUid', $result);
            $this->assertArrayHasKey('jobName', $result);
            $this->assertArrayHasKey('targetLocaleIds', $result);
            $this->assertArrayHasKey('description', $result);
            $this->assertArrayHasKey('dueDate', $result);
            $this->assertArrayHasKey('referenceNumber', $result);
            $this->assertArrayHasKey('callbackUrl', $result);
            $this->assertArrayHasKey('callbackMethod', $result);
            $this->assertArrayHasKey('createdDate', $result);
            $this->assertArrayHasKey('modifiedDate', $result);
            $this->assertArrayHasKey('createdByUserUid', $result);
            $this->assertArrayHasKey('modifiedByUserUid', $result);
            $this->assertArrayHasKey('firstCompletedDate', $result);
            $this->assertArrayHasKey('lastCompletedDate', $result);
            $this->assertArrayHasKey('jobStatus', $result);
            $this->assertArrayHasKey('sourceFiles', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for job get.
     */
    public function testJobsApiGetJob()
    {
        try {
            $result = $this->jobsApi->getJob($this->jobId);

            $this->assertArrayHasKey('translationJobUid', $result);
            $this->assertArrayHasKey('jobName', $result);
            $this->assertArrayHasKey('targetLocaleIds', $result);
            $this->assertArrayHasKey('description', $result);
            $this->assertArrayHasKey('dueDate', $result);
            $this->assertArrayHasKey('referenceNumber', $result);
            $this->assertArrayHasKey('callbackUrl', $result);
            $this->assertArrayHasKey('callbackMethod', $result);
            $this->assertArrayHasKey('createdDate', $result);
            $this->assertArrayHasKey('modifiedDate', $result);
            $this->assertArrayHasKey('createdByUserUid', $result);
            $this->assertArrayHasKey('modifiedByUserUid', $result);
            $this->assertArrayHasKey('firstCompletedDate', $result);
            $this->assertArrayHasKey('lastCompletedDate', $result);
            $this->assertArrayHasKey('jobStatus', $result);
            $this->assertArrayHasKey('sourceFiles', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for add file to job get.
     */
    public function testJobsApiAddFileToJob()
    {
        try {
            $this->fileApi->uploadFile('tests/resources/test.xml', 'test.xml', 'xml');
            $params = new AddFileToJobParameters();
            $params->setFileUri('test.xml');
            $this->jobsApi->addFileToJobSync($this->jobId, $params);
            $this->fileApi->deleteFile('test.xml');
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for jobs search.
     */
    public function testJobsApiSearchJob()
    {
        try {
            $searchParameters = new SearchJobsParameters();
            $searchParameters->setFileUris([
                'some_file_to_search.xml',
            ]);
            $result = $this->jobsApi->searchJobs($searchParameters);

            $this->assertArrayHasKey('totalCount', $result);
            $this->assertArrayHasKey('items', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for job authorize.
     */
    public function testJobsApiAuthorizeJob()
    {
        try {
            $this->fileApi->uploadFile('tests/resources/test.xml', 'test.xml', 'xml');
            $params = new AddFileToJobParameters();
            $params->setFileUri('test.xml');
            $this->jobsApi->addFileToJobSync($this->jobId, $params);
            $this->jobsApi->authorizeJob($this->jobId);
            $this->fileApi->deleteFile('test.xml');
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for add locale to job get.
     */
    public function testJobsApiAddLocaleToJob()
    {
        try {
            $params = new AddLocaleToJobParameters();
            $params->setSyncContent(false);
            $this->jobsApi->addLocaleToJobSync($this->jobId, 'fr-FR', $params);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test checks status of async process.
     */
    public function testJobsApiCheckAsynchronousProcessingStatus()
    {
        try {
            $this->fileApi->uploadFile('tests/resources/test.xml', 'test.xml', 'xml');

            $params = new AddLocaleToJobParameters();
            $params->setSyncContent(false);
            $this->jobsApi->addLocaleToJobSync($this->jobId, 'fr-FR', $params);

            $params = new AddFileToJobParameters();
            $params->setTargetLocales(['fr-FR']);
            $params->setFileUri('test.xml');
            $this->jobsApi->addFileToJobSync($this->jobId, $params);

            $params = new AddLocaleToJobParameters();
            $params->setSyncContent(true);
            $this->jobsApi->addLocaleToJobSync($this->jobId, 'de-DE', $params);

            $this->fileApi->deleteFile('test.xml');
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

}
