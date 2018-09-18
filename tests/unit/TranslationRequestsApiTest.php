<?php

namespace Smartling\Tests\Unit;


use Smartling\TranslationRequests\Params\CreateTranslationRequestParams;
use Smartling\TranslationRequests\Params\SearchTranslationRequestParams;
use Smartling\TranslationRequests\Params\UpdateTranslationRequestParams;
use Smartling\TranslationRequests\TranslationRequestsApi;

class TranslationRequestsApiTest extends ApiTestAbstract
{
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->prepareTranslationRequestsApiMock();
    }

    private function prepareTranslationRequestsApiMock()
    {
        $this->object = $this->getMockBuilder('Smartling\TranslationRequests\TranslationRequestsApi')
            ->setMethods(null)
            ->setConstructorArgs([
                $this->projectId,
                $this->client,
                null,
                TranslationRequestsApi::ENDPOINT_URL,
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
     * @covers \Smartling\TranslationRequests\TranslationRequestsApi::createTranslationRequest
     */
    public function testCreateTranslationRequest()
    {

        $createParams = (new CreateTranslationRequestParams())
            ->setOriginalAssetKey(['a' => '1'])
            ->setTitle('Submission 1')
            ->setFileUri('/posts/hello-world_1_1_post.xml')
            ->setOriginalLocaleId('en-US');

        $testRawResponse = json_encode(
            [
                "response" => [
                    "code" => "SUCCESS",
                    "data" => [
                        "translationRequestUid" => "8264fd9133d3",
                        "projectId" => $this->projectId,
                        "bucketName" => "name",
                        "originalAssetId" => ["a" => "1"],
                        "title" => "Submission 1",
                        "fileUri" => "/posts/hello-world_1_1_post.xml",
                        "totalWordCount" => "0",
                        "totalStringCount" => "0",
                        "contentHash" => null,
                        "originalLocaleId" => "en-US",
                        "outdated" => "0",
                        "customOriginalData" => null,
                        "createdDate" => "2018-07-31T10:37:20.839Z",
                        "modifiedDate" => "2018-07-31T10:37:20.839Z",
                        "translationSubmissions" => []
                    ]
                ]
            ]
        );

        $this->mockClientResponse(200, $testRawResponse);

        $testExpectedResponse = json_decode($testRawResponse, true)['response']['data'];

        $bucketName = 'name';
        $endpointUrl = vsprintf('%s/%s/buckets/%s/translation-requests',
            [TranslationRequestsApi::ENDPOINT_URL, $this->projectId, $bucketName]);


        $requestStructure = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => vsprintf('%s %s',
                    [$this->authProvider->getTokenType(), $this->authProvider->getAccessToken()]),
            ],
            'exceptions' => false,
            'json' => $createParams->exportToArray()
        ];

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, $requestStructure)
            ->willReturn($this->responseMock);

        $response = $this->object->createTranslationRequest($bucketName, $createParams);
        self::assertEquals($testExpectedResponse, $response);
    }

    /**
     * @covers \Smartling\TranslationRequests\TranslationRequestsApi::updateTranslationRequest
     */
    public function testUpdateTranslationRequest()
    {

        $translationRequestUid = '8264fd9133d3';

        $updateParams = (new UpdateTranslationRequestParams())->setTitle('Submission 2');

        $testRawResponse = json_encode(
            [
                "response" => [
                    "code" => "SUCCESS",
                    "data" => [
                        "translationRequestUid" => $translationRequestUid,
                        "projectId" => $this->projectId,
                        "bucketName" => "name",
                        "originalAssetId" => ["a" => "1"],
                        "title" => "Submission 2",
                        "fileUri" => "/posts/hello-world_1_1_post.xml",
                        "totalWordCount" => "0",
                        "totalStringCount" => "0",
                        "contentHash" => null,
                        "originalLocaleId" => "en-US",
                        "outdated" => "0",
                        "customOriginalData" => null,
                        "createdDate" => "2018-07-31T10:37:20.839Z",
                        "modifiedDate" => "2018-07-31T10:37:20.839Z",
                        "translationSubmissions" => []
                    ]
                ]
            ]
        );

        $this->mockClientResponse(200, $testRawResponse);

        $testExpectedResponse = json_decode($testRawResponse, true)['response']['data'];

        $bucketName = 'name';
        $endpointUrl = vsprintf('%s/%s/buckets/%s/translation-requests/%s',
            [TranslationRequestsApi::ENDPOINT_URL, $this->projectId, $bucketName, $translationRequestUid]);


        $requestStructure = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => vsprintf('%s %s',
                    [$this->authProvider->getTokenType(), $this->authProvider->getAccessToken()]),
            ],
            'exceptions' => false,
            'json' => $updateParams->exportToArray()
        ];

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('put', $endpointUrl, $requestStructure)
            ->willReturn($this->responseMock);

        $response = $this->object->updateTranslationRequest($bucketName, $translationRequestUid, $updateParams);
        self::assertEquals($testExpectedResponse, $response);
    }

    /**
     * @covers \Smartling\TranslationRequests\TranslationRequestsApi::getTranslationRequest
     */
    public function testGetTranslationRequest()
    {

        $translationRequestUid = '8264fd9133d3';

        $testRawResponse = json_encode(
            [
                "response" => [
                    "code" => "SUCCESS",
                    "data" => [
                        "translationRequestUid" => $translationRequestUid,
                        "projectId" => $this->projectId,
                        "bucketName" => "name",
                        "originalAssetId" => ["a" => "1"],
                        "title" => "Submission 2",
                        "fileUri" => "/posts/hello-world_1_1_post.xml",
                        "totalWordCount" => "0",
                        "totalStringCount" => "0",
                        "contentHash" => null,
                        "originalLocaleId" => "en-US",
                        "outdated" => "0",
                        "customOriginalData" => null,
                        "createdDate" => "2018-07-31T10:37:20.839Z",
                        "modifiedDate" => "2018-07-31T10:37:20.839Z",
                        "translationSubmissions" => []
                    ]
                ]
            ]
        );

        $this->mockClientResponse(200, $testRawResponse);

        $testExpectedResponse = json_decode($testRawResponse, true)['response']['data'];

        $bucketName = 'name';
        $endpointUrl = vsprintf('%s/%s/buckets/%s/translation-requests/%s',
            [TranslationRequestsApi::ENDPOINT_URL, $this->projectId, $bucketName, $translationRequestUid]);


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

        $response = $this->object->getTranslationRequest($bucketName, $translationRequestUid);
        self::assertEquals($testExpectedResponse, $response);
    }

    public function searchTranslationRequestTestDataProvider()
    {
        return [
            [
                (new SearchTranslationRequestParams())->setFileUri('file.xml'),
                [
                    'fileUri' => 'file.xml'
                ],
                [
                    "response" => [
                        "code" => "SUCCESS",
                        "data" => [
                            [
                                "translationRequestUid" => 'abc',
                                "projectId" => $this->projectId,
                                "bucketName" => "name",
                                "originalAssetId" => ["a" => "1"],
                                "title" => "Submission 2",
                                "fileUri" => "file.xml",
                                "totalWordCount" => "0",
                                "totalStringCount" => "0",
                                "contentHash" => null,
                                "originalLocaleId" => "en-US",
                                "outdated" => "0",
                                "customOriginalData" => null,
                                "createdDate" => "2018-07-31T10:37:20.839Z",
                                "modifiedDate" => "2018-07-31T10:37:20.839Z",
                                "translationSubmissions" => []
                            ]
                        ]
                    ]

                ]
            ],
            [
                (new SearchTranslationRequestParams())->setOriginalAssetKey(["a" => "1"]),
                [
                    'originalAssetKey' => json_encode(["a" => "1"])
                ],
                [
                    "response" => [
                        "code" => "SUCCESS",
                        "data" => [
                            [
                                "translationRequestUid" => 'abc',
                                "projectId" => $this->projectId,
                                "bucketName" => "name",
                                "originalAssetId" => ["a" => "1"],
                                "title" => "Submission 2",
                                "fileUri" => "file.xml",
                                "totalWordCount" => "0",
                                "totalStringCount" => "0",
                                "contentHash" => null,
                                "originalLocaleId" => "en-US",
                                "outdated" => "0",
                                "customOriginalData" => null,
                                "createdDate" => "2018-07-31T10:37:20.839Z",
                                "modifiedDate" => "2018-07-31T10:37:20.839Z",
                                "translationSubmissions" => []
                            ]
                        ]
                    ]

                ]
            ],
            [
                (new SearchTranslationRequestParams())->setOriginalAssetKey(["a" => "1"])->setFileUri('%.xml'),
                (new SearchTranslationRequestParams())->setOriginalAssetKey(["a" => "1"])->setFileUri('%.xml')->exportToArray(),
                [
                    "response" => [
                        "code" => "SUCCESS",
                        "data" => [
                            [
                                "translationRequestUid" => 'abc',
                                "projectId" => $this->projectId,
                                "bucketName" => "name",
                                "originalAssetId" => ["a" => "1"],
                                "title" => "Submission 2",
                                "fileUri" => "file.xml",
                                "totalWordCount" => "0",
                                "totalStringCount" => "0",
                                "contentHash" => null,
                                "originalLocaleId" => "en-US",
                                "outdated" => "0",
                                "customOriginalData" => null,
                                "createdDate" => "2018-07-31T10:37:20.839Z",
                                "modifiedDate" => "2018-07-31T10:37:20.839Z",
                                "translationSubmissions" => []
                            ]
                        ]
                    ]

                ]
            ],
            [
                (new SearchTranslationRequestParams())
                    ->setOriginalAssetKey(["a" => "1"])
                    ->setFileUri("%.xml")
                    ->setOutdated(0)
                    ->setCustomOriginalData(["b" => "2"])
                    ->setTargetAssetKey(["c" => "3"])
                    ->setTargetLocaleId('es')
                    ->setState('New')
                    ->setSubmitterName('wp')
                    ->setCustomTranslationData(["d" => "4"])
                    ->setLimit(5)
                    ->setOffset(6),
                (new SearchTranslationRequestParams())
                    ->setOriginalAssetKey(["a" => "1"])
                    ->setFileUri("%.xml")
                    ->setOutdated(0)
                    ->setCustomOriginalData(["b" => "2"])
                    ->setTargetAssetKey(["c" => "3"])
                    ->setTargetLocaleId('es')
                    ->setState('New')
                    ->setSubmitterName('wp')
                    ->setCustomTranslationData(["d" => "4"])
                    ->setLimit(5)
                    ->setOffset(6)
                    ->exportToArray(),

                [
                    "response" => [
                        "code" => "SUCCESS",
                        "data" => [
                            "items" => []
                        ]
                    ]

                ]
            ]

        ];
    }

    /**
     * @covers       \Smartling\TranslationRequests\TranslationRequestsApi::searchTranslationRequests
     * @dataProvider searchTranslationRequestTestDataProvider
     * @param SearchTranslationRequestParams $searchParams
     * @param array $queryParams
     * @param array $rawResponse
     * @throws \Smartling\Exceptions\InvalidAccessTokenException
     */
    public function testSearchTranslationRequests(SearchTranslationRequestParams $searchParams, array $queryParams, array $rawResponse)
    {
        $testRawResponse = json_encode($rawResponse);

        $this->mockClientResponse(200, $testRawResponse);

        $testExpectedResponse = $rawResponse['response']['data'];

        $bucketName = 'name';
        $endpointUrl = vsprintf('%s/%s/buckets/%s/translation-requests',
            [TranslationRequestsApi::ENDPOINT_URL, $this->projectId, $bucketName]);

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

        $response = $this->object->searchTranslationRequests($bucketName, $searchParams);
        self::assertEquals($testExpectedResponse, $response);
    }
}
