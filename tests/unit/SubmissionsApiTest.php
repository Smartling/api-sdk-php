<?php

namespace Smartling\Tests\Unit;


use Smartling\Submissions\Params\SearchSubmissionsParams;
use Smartling\Submissions\SubmissionsApi;

class SubmissionsApiTest extends ApiTestAbstract
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->prepareSubmissionsApiMock();
    }

    private function prepareSubmissionsApiMock()
    {
        $this->object = $this->getMockBuilder('Smartling\Submissions\SubmissionsApi')
            ->setMethods(null)
            ->setConstructorArgs([
                $this->projectId,
                $this->client,
                null,
                SubmissionsApi::ENDPOINT_URL,
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

    protected function mockClientResponse($code = 200, $body = [])
    {
        $this->responseMock = $this->getMockBuilder('GuzzleHttp\Psr7\Response')
            ->setMethods(
                array_merge(
                    self::$responseInterfaceMethods,
                    self::$messageInterfaceMethods
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->responseMock->expects(self::any())->method('json')->willReturn(json_decode($body, true));
        $this->responseMock->expects(self::any())->method('getBody')->willReturn($body);

        $this->responseMock->expects($this->any())->method('getStatusCode')->willReturn($code);
    }

    /**
     * @covers \Smartling\Submissions\SubmissionsApi::createSubmission
     */
    public function testCreateSubmission()
    {

        $testRequestBody = [
            'original_asset_id' => ['a' => '1'],
            'title' => 'Submission 1',
            'fileUri' => '/posts/hello-world_1_1_post.xml',
            'original_locale' => 'en-US'
        ];

        $testRawResponse = json_encode(
            [
                "response" => [
                    "code" => "SUCCESS",
                    "data" => [
                        "submission_uid" => "8264fd9133d3",
                        "project_id" => $this->projectId,
                        "bucket_name" => "name",
                        "original_asset_id" => ["a" => "1"],
                        "title" => "Submission 1",
                        "fileUri" => "/posts/hello-world_1_1_post.xml",
                        "total_word_count" => "0",
                        "total_string_count" => "0",
                        "content_hash" => null,
                        "original_locale" => "en-US",
                        "outdated" => "0",
                        "last_modified" => null,
                        "custom_original_data" => null,
                        "createdAt" => "2018-07-31T10:37:20.839Z",
                        "updatedAt" => "2018-07-31T10:37:20.839Z",
                        "details" => []
                    ]
                ]
            ]
        );

        $this->mockClientResponse(200, $testRawResponse);

        $testExpectedResponse = json_decode($testRawResponse, true)['response']['data'];

        $bucketName = 'name';
        $endpointUrl = vsprintf('%s/%s/buckets/%s/submissions',
            [SubmissionsApi::ENDPOINT_URL, $this->projectId, $bucketName]);


        $requestStructure = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => vsprintf('%s %s',
                    [$this->authProvider->getTokenType(), $this->authProvider->getAccessToken()]),
            ],
            'exceptions' => false,
            'json' => $testRequestBody
        ];

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, $requestStructure)
            ->willReturn($this->responseMock);

        $response = $this->object->createSubmission($bucketName, $testRequestBody);
        self::assertEquals($testExpectedResponse, $response);
    }

    /**
     * @covers \Smartling\Submissions\SubmissionsApi::updateSubmission
     */
    public function testUpdateSubmission()
    {

        $submissionUid = '8264fd9133d3';

        $testRequestBody = [
            'title' => 'Submission 2'
        ];

        $testRawResponse = json_encode(
            [
                "response" => [
                    "code" => "SUCCESS",
                    "data" => [
                        "submission_uid" => $submissionUid,
                        "project_id" => $this->projectId,
                        "bucket_name" => "name",
                        "original_asset_id" => ["a" => "1"],
                        "title" => "Submission 2",
                        "fileUri" => "/posts/hello-world_1_1_post.xml",
                        "total_word_count" => "0",
                        "total_string_count" => "0",
                        "content_hash" => null,
                        "original_locale" => "en-US",
                        "outdated" => "0",
                        "last_modified" => null,
                        "custom_original_data" => null,
                        "createdAt" => "2018-07-31T10:37:20.839Z",
                        "updatedAt" => "2018-07-31T10:37:20.839Z",
                        "details" => []
                    ]
                ]
            ]
        );

        $this->mockClientResponse(200, $testRawResponse);

        $testExpectedResponse = json_decode($testRawResponse, true)['response']['data'];

        $bucketName = 'name';
        $endpointUrl = vsprintf('%s/%s/buckets/%s/submissions/%s',
            [SubmissionsApi::ENDPOINT_URL, $this->projectId, $bucketName, $submissionUid]);


        $requestStructure = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => vsprintf('%s %s',
                    [$this->authProvider->getTokenType(), $this->authProvider->getAccessToken()]),
            ],
            'exceptions' => false,
            'json' => $testRequestBody
        ];

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('put', $endpointUrl, $requestStructure)
            ->willReturn($this->responseMock);

        $response = $this->object->updateSubmission($bucketName, $submissionUid, $testRequestBody);
        self::assertEquals($testExpectedResponse, $response);
    }

    /**
     * @covers \Smartling\Submissions\SubmissionsApi::getSubmission
     */
    public function testGetSubmission()
    {

        $submissionUid = '8264fd9133d3';

        $testRawResponse = json_encode(
            [
                "response" => [
                    "code" => "SUCCESS",
                    "data" => [
                        "submission_uid" => $submissionUid,
                        "project_id" => $this->projectId,
                        "bucket_name" => "name",
                        "original_asset_id" => ["a" => "1"],
                        "title" => "Submission 2",
                        "fileUri" => "/posts/hello-world_1_1_post.xml",
                        "total_word_count" => "0",
                        "total_string_count" => "0",
                        "content_hash" => null,
                        "original_locale" => "en-US",
                        "outdated" => "0",
                        "last_modified" => null,
                        "custom_original_data" => null,
                        "createdAt" => "2018-07-31T10:37:20.839Z",
                        "updatedAt" => "2018-07-31T10:37:20.839Z",
                        "details" => []
                    ]
                ]
            ]
        );

        $this->mockClientResponse(200, $testRawResponse);

        $testExpectedResponse = json_decode($testRawResponse, true)['response']['data'];

        $bucketName = 'name';
        $endpointUrl = vsprintf('%s/%s/buckets/%s/submissions/%s',
            [SubmissionsApi::ENDPOINT_URL, $this->projectId, $bucketName, $submissionUid]);


        $requestStructure = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => vsprintf('%s %s',
                    [$this->authProvider->getTokenType(), $this->authProvider->getAccessToken()]),
            ],
            'exceptions' => false,
            'query' => []
        ];

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('get', $endpointUrl, $requestStructure)
            ->willReturn($this->responseMock);

        $response = $this->object->getSubmission($bucketName, $submissionUid);
        self::assertEquals($testExpectedResponse, $response);
    }

    public function searchSubmissionTestDataProvider()
    {
        return [
            [
                (new SearchSubmissionsParams())->setFileUri('file.xml'),
                [
                    'fileUri' => 'file.xml'
                ],
                [
                    "response" => [
                        "code" => "SUCCESS",
                        "data" => [
                            [
                                "submission_uid" => 'abc',
                                "project_id" => $this->projectId,
                                "bucket_name" => "name",
                                "original_asset_id" => ["a" => "1"],
                                "title" => "Submission 2",
                                "fileUri" => "file.xml",
                                "total_word_count" => "0",
                                "total_string_count" => "0",
                                "content_hash" => null,
                                "original_locale" => "en-US",
                                "outdated" => "0",
                                "last_modified" => null,
                                "custom_original_data" => null,
                                "createdAt" => "2018-07-31T10:37:20.839Z",
                                "updatedAt" => "2018-07-31T10:37:20.839Z",
                                "details" => []
                            ]
                        ]
                    ]

                ]
            ],
            [
                (new SearchSubmissionsParams())->setOriginalAssetId(["a" => "1"]),
                [
                    'original_asset_id' => json_encode(["a" => "1"])
                ],
                [
                    "response" => [
                        "code" => "SUCCESS",
                        "data" => [
                            [
                                "submission_uid" => 'abc',
                                "project_id" => $this->projectId,
                                "bucket_name" => "name",
                                "original_asset_id" => ["a" => "1"],
                                "title" => "Submission 2",
                                "fileUri" => "file.xml",
                                "total_word_count" => "0",
                                "total_string_count" => "0",
                                "content_hash" => null,
                                "original_locale" => "en-US",
                                "outdated" => "0",
                                "last_modified" => null,
                                "custom_original_data" => null,
                                "createdAt" => "2018-07-31T10:37:20.839Z",
                                "updatedAt" => "2018-07-31T10:37:20.839Z",
                                "details" => []
                            ]
                        ]
                    ]

                ]
            ],

            [
                (new SearchSubmissionsParams())->setOriginalAssetId(["a" => "1"])->setFileUri('%.xml'),
                [
                    'original_asset_id' => json_encode(["a" => "1"]),
                    'fileUri' => '%.xml'
                ],
                [
                    "response" => [
                        "code" => "SUCCESS",
                        "data" => [
                            [
                                "submission_uid" => 'abc',
                                "project_id" => $this->projectId,
                                "bucket_name" => "name",
                                "original_asset_id" => ["a" => "1"],
                                "title" => "Submission 2",
                                "fileUri" => "file.xml",
                                "total_word_count" => "0",
                                "total_string_count" => "0",
                                "content_hash" => null,
                                "original_locale" => "en-US",
                                "outdated" => "0",
                                "last_modified" => null,
                                "custom_original_data" => null,
                                "createdAt" => "2018-07-31T10:37:20.839Z",
                                "updatedAt" => "2018-07-31T10:37:20.839Z",
                                "details" => []
                            ]
                        ]
                    ]

                ]
            ],
            [
                (new SearchSubmissionsParams())
                    ->setOriginalAssetId(["a" => "1"])
                    ->setFileUri("%.xml")
                    ->setOutdated(0)
                    ->setCustomOriginalData(["b" => "2"])
                    ->setTranslationAssetId(["c" => "3"])
                    ->setTargetLocale('es')
                    ->setState('New')
                    ->setSubmitter('wp')
                    ->setCustomTranslationData(["d" => "4"])
                    ->setLimit(5)
                    ->setOffset(6),
                (new SearchSubmissionsParams())
                    ->setOriginalAssetId(["a" => "1"])
                    ->setFileUri("%.xml")
                    ->setOutdated(0)
                    ->setCustomOriginalData(["b" => "2"])
                    ->setTranslationAssetId(["c" => "3"])
                    ->setTargetLocale('es')
                    ->setState('New')
                    ->setSubmitter('wp')
                    ->setCustomTranslationData(["d" => "4"])
                    ->setLimit(5)
                    ->setOffset(6)
                    ->exportToArray(),

                [
                    "response" => [
                        "code" => "SUCCESS",
                        "data" => []
                    ]

                ]
            ]

        ];
    }

    /**
     * @covers       \Smartling\Submissions\SubmissionsApi::searchSubmissions
     * @dataProvider searchSubmissionTestDataProvider
     */
    public function testSearchSubmissios(SearchSubmissionsParams $searchParams, array $queryParams, array $rawResponse)
    {

        $testRawResponse = json_encode($rawResponse);

        $this->mockClientResponse(200, $testRawResponse);

        $testExpectedResponse = $rawResponse['response']['data'];

        $bucketName = 'name';
        $endpointUrl = vsprintf('%s/%s/buckets/%s/submissions',
            [SubmissionsApi::ENDPOINT_URL, $this->projectId, $bucketName]);

        $requestStructure = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => vsprintf('%s %s',
                    [$this->authProvider->getTokenType(), $this->authProvider->getAccessToken()]),
            ],
            'exceptions' => false,
            'query' => $queryParams
        ];

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('get', $endpointUrl, $requestStructure)
            ->willReturn($this->responseMock);

        $response = $this->object->searchSubmissions($bucketName, $searchParams);
        self::assertEquals($testExpectedResponse, $response);
    }
}
