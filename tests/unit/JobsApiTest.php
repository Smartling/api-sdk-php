<?php

namespace Smartling\Tests\Unit;

use Smartling\Jobs\JobsApi;
use Smartling\Jobs\Params\SearchJobsParameters;

/**
 * Test class for Smartling\Jobs\JobsApi.
 */
class JobsApiTest extends ApiTestAbstract
{

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
     * @covers \Smartling\Jobs\JobsApi::searchJobs
     */
    public function testSearchJobs()
    {
        $fileToSearch = 'some_file_to_search.xml';
        $searchParameters = new SearchJobsParameters();
        $searchParameters->setFileUris([
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

        $this->object->searchJobs($searchParameters);
    }

}
