<?php

namespace Smartling\Tests\Unit;

use DateTime;
use DateTimeZone;
use Smartling\Jobs\JobsApi;
use Smartling\Jobs\Params\AddFileToJobParameters;
use Smartling\Jobs\Params\AddLocaleToJobParameters;
use Smartling\Jobs\Params\CancelJobParameters;
use Smartling\Jobs\Params\CreateJobParameters;
use Smartling\Jobs\Params\ListJobsParameters;
use Smartling\Jobs\Params\SearchJobsParameters;
use Smartling\Jobs\Params\UpdateJobParameters;

/**
 * Test class for Smartling\Jobs\JobsApi.
 */
class JobsApiTest extends ApiTestAbstract
{

    /**
     * @var string
     */
    protected $responseAsync = '{"response":{"data":{"message":"This process will continue asynchronously and may take up to a few minutes.", "url":"https://api.smartling.com/jobs-api/v3/projects/{projectId}/jobs/{translationJobId}/processes/{processUid}"},"code":"ACCEPTED"}}';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->prepareJobsApiMock();
    }

    private function prepareJobsApiMock()
    {
        $this->object = $this->getMockBuilder('Smartling\Jobs\JobsApi')
            ->setMethods(NULL)
            ->setConstructorArgs([
                $this->projectId,
                $this->client,
                null,
                JobsApi::ENDPOINT_URL,
            ])
            ->getMock();

        $this->invokeMethod(
            $this->object,
            'setAuth',
            [
                $this->authProvider
            ]
        );
    }

    /**
     * @covers \Smartling\Jobs\JobsApi::createJob
     */
    public function testCreateJob() {
        $name = 'Test Job Name';
        $description = 'Test Job Description';
        $dueDate = DateTime::createFromFormat('Y-m-d H:i:s', '2020-01-01 19:19:17', new DateTimeZone('UTC'));
        $locales = ['es', 'fr'];
        $params = new CreateJobParameters();
        $params->setName($name);
        $params->setDescription($description);
        $params->setDueDate($dueDate);
        $params->setTargetLocales($locales);
        $endpointUrl = vsprintf('%s/%s/jobs', [
            JobsApi::ENDPOINT_URL,
            $this->projectId
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'json' => [
                    'jobName' => $name,
                    'description' => $description,
                    'dueDate' => $dueDate->format('Y-m-d\TH:i:s\Z'),
                    'targetLocaleIds' => $locales,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->createJob($params);
    }

    /**
     * @covers \Smartling\Jobs\JobsApi::updateJob
     */
    public function testUpdateJob() {
        $jobId = 'Some job id';
        $name = 'Test Job Name Updated';
        $description = 'Test Job Description Updated';
        $dueDate = DateTime::createFromFormat('Y-m-d H:i:s', '2030-01-01 19:19:17', new DateTimeZone('UTC'));
        $params = new UpdateJobParameters();
        $params->setName($name);
        $params->setDescription($description);
        $params->setDueDate($dueDate);
        $endpointUrl = vsprintf('%s/%s/jobs/%s', [
            JobsApi::ENDPOINT_URL,
            $this->projectId,
            $jobId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('put', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'json' => [
                    'jobName' => $name,
                    'description' => $description,
                    'dueDate' => $dueDate->format('Y-m-d\TH:i:s\Z'),
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->updateJob($jobId, $params);
    }

    /**
     * @covers \Smartling\Jobs\JobsApi::cancelJobSync
     */
    public function testCancelJob() {
        $jobId = 'Some job id';
        $reason = 'Some reason';
        $params = new CancelJobParameters();
        $params->setReason($reason);
        $endpointUrl = vsprintf('%s/%s/jobs/%s/cancel', [
            JobsApi::ENDPOINT_URL,
            $this->projectId,
            $jobId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'json' => [
                    'reason' => $reason,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->cancelJobSync($jobId, $params);
    }

    /**
     * @covers \Smartling\Jobs\JobsApi::listJobs
     */
    public function testListJobs() {
        $name = 'Test Job Name Updated';
        $limit = 1;
        $offset = 2;
        $params = new ListJobsParameters();
        $params->setName($name);
        $params->setLimit($limit);
        $params->setOffset($offset);
        $endpointUrl = vsprintf('%s/%s/jobs', [
            JobsApi::ENDPOINT_URL,
            $this->projectId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'query' => [
                    'jobName' => $name,
                    'limit' => $limit,
                    'offset' => $offset,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->listJobs($params);
    }

    /**
     * @covers \Smartling\Jobs\JobsApi::getJob
     */
    public function testGetJob() {
        $jobId = 'Some job id';
        $endpointUrl = vsprintf('%s/%s/jobs/%s', [
            JobsApi::ENDPOINT_URL,
            $this->projectId,
            $jobId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $this->object->getJob($jobId);
    }

    /**
     * @covers \Smartling\Jobs\JobsApi::authorizeJob
     */
    public function testAuthorizeJob() {
        $jobId = 'Some job id';
        $endpointUrl = vsprintf('%s/%s/jobs/%s/authorize', [
            JobsApi::ENDPOINT_URL,
            $this->projectId,
            $jobId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                    'Content-Type' => 'application/json',
                ],
                'exceptions' => FALSE,
                'json' => new \stdClass(),
            ])
            ->willReturn($this->responseMock);

        $this->object->authorizeJob($jobId);
    }

    /**
     * @covers \Smartling\Jobs\JobsApi::addFileToJobSync
     */
    public function testAddFileToJob() {
        $jobId = 'Some job id';
        $fileUri = 'some_file.xml';
        $params = new AddFileToJobParameters();
        $params->setFileUri($fileUri);
        $endpointUrl = vsprintf('%s/%s/jobs/%s/file/add', [
            JobsApi::ENDPOINT_URL,
            $this->projectId,
            $jobId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'json' => [
                    'fileUri' => $fileUri,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->addFileToJobSync($jobId, $params);
    }

    /**
     * @covers \Smartling\Jobs\JobsApi::searchJobs
     */
    public function testSearchJobs()
    {
        $fileToSearch = 'some_file_to_search.xml';
        $params = new SearchJobsParameters();
        $params->setFileUris([
            $fileToSearch,
        ]);

        $endpointUrl = vsprintf('%s/%s/jobs/search', [JobsApi::ENDPOINT_URL, $this->projectId]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'json' => [
                    'fileUris' => [
                        $fileToSearch,
                    ],
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->searchJobs($params);
    }

    /**
     * @covers \Smartling\Jobs\JobsApi::addLocaleToJobSync
     */
    public function testAddLocaleToJob() {
        $jobId = 'Some job id';
        $localeId = 'Some locale id';
        $params = new AddLocaleToJobParameters();
        $params->setSyncContent(false);
        $endpointUrl = vsprintf('%s/%s/jobs/%s/locales/%s', [
            JobsApi::ENDPOINT_URL,
            $this->projectId,
            $jobId,
            $localeId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'json' => [
                    'syncContent' => false,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->addLocaleToJobSync($jobId, $localeId, $params);
    }

    /**
     * @covers \Smartling\Jobs\JobsApi::checkAsynchronousProcessingStatus
     */
    public function testCheckAsynchronousProcessingStatus() {
        $jobId = 'Some job id';
        $processId = 'Some process id';
        $endpointUrl = vsprintf('%s/%s/jobs/%s/processes/%s', [
            JobsApi::ENDPOINT_URL,
            $this->projectId,
            $jobId,
            $processId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $this->object->checkAsynchronousProcessingStatus($jobId, $processId);
    }

}
