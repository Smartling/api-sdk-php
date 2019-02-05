<?php

namespace Smartling\Tests\Unit;

use Smartling\File\FileApi;
use Smartling\File\Params\DownloadFileParameters;
use Smartling\File\Params\UploadFileParameters;

/**
 * Test class for Smartling\File\FileApi.
 */
class FileApiTest extends ApiTestAbstract
{
    private function prepareFileApiMock()
    {
        $this->object = $this->getMockBuilder('Smartling\File\FileApi')
            ->setMethods(['readFile'])
            ->setConstructorArgs([
                $this->projectId,
                $this->client,
                null,
                FileApi::ENDPOINT_URL,
            ])
            ->getMock();

        $this->object->expects($this->any())
            ->method('readFile')
            ->willReturn($this->streamPlaceholder);

        $this->invokeMethod(
            $this->object,
            'setAuth',
            [
                $this->authProvider
            ]
        );
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->prepareHttpClientMock();
        $this->prepareAuthProviderMock();
        $this->prepareFileApiMock();
    }

    /**
     * Tests constructor.
     *
     * @param string $projectId
     *   Project Id string.
     * @param \GuzzleHttp\ClientInterface $client
     *   Mock of Guzzle http client instance.
     * @param string|null $expected_base_url
     *   Base Url string that will be used as based url.
     *
     * @covers       \Smartling\File\FileApi::__construct
     *
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($projectId, $client, $expected_base_url)
    {
        $this->prepareClientResponseMock();
        $fileApi = new FileApi($projectId, $client, null, $expected_base_url);

        $this->assertEquals(rtrim($expected_base_url, '/') . '/' . $projectId,
            $this->invokeMethod($fileApi, 'getBaseUrl'));
        $this->assertEquals($projectId, $this->invokeMethod($fileApi, 'getProjectId'));
        $this->assertEquals($client, $this->invokeMethod($fileApi, 'getHttpClient'));
    }

    /**
     * Data provider for testConstructor method.
     *
     * Tests if base url will be set correctly depending on income baseurl
     * and mode.
     *
     * @return array
     */
    public function constructorDataProvider()
    {
        $this->prepareHttpClientMock();

        $mockedClient = $this->client;

        return [
            ['product-id', $mockedClient, FileApi::ENDPOINT_URL],
            ['product-id', $mockedClient, FileApi::ENDPOINT_URL],
            ['product-id', $mockedClient, FileApi::ENDPOINT_URL . '/'],
            ['product-id', $mockedClient, 'https://www.google.com.ua/webhp'],
        ];
    }

    /**
     * @covers \Smartling\File\FileApi::uploadFile
     */
    public function testUploadFile()
    {
        $this->prepareClientResponseMock();
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('post', FileApi::ENDPOINT_URL . '/' . $this->projectId . '/file', [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'multipart' => [
                    [
                        'name' => 'authorize',
                        'contents' => 0,
                    ],
                    [
                        'name' => 'smartling.client_lib_id',
                        'contents' => '{"client":"smartling-api-sdk-php","version":"3.6.2"}',
                    ],
                    [
                        'name' => 'localeIdsToAuthorize[]',
                        'contents' => 'es',
                    ],
                    [
                        'name' => 'file',
                        'contents' => $this->streamPlaceholder,
                    ],
                    [
                        'name' => 'fileUri',
                        'contents' => 'test.xml',
                    ],
                    [
                        'name' => 'fileType',
                        'contents' => 'xml',
                    ],
                ],
            ])
            ->willReturn($this->responseMock);

        $params = new UploadFileParameters();
        $params->setAuthorized(true);
        $params->setLocalesToApprove('es');

        $this->object->uploadFile('tests/resources/test.xml', 'test.xml', 'xml', $params);
    }


    /**
     * Tests AutoAuthorize logic
     */
    public function testFileUploadParams()
    {
        $this->prepareClientResponseMock();
        $fileUploadParams = new UploadFileParameters();

        $fileUploadParams->setAuthorized(false);
        $exportedSettings = $fileUploadParams->exportToArray();
        $this->assertEquals($exportedSettings['authorize'], false);

        $fileUploadParams->setAuthorized(true);
        $exportedSettings = $fileUploadParams->exportToArray();
        $this->assertEquals($exportedSettings['authorize'], true);

        $fileUploadParams->setLocalesToApprove('locale');

        $fileUploadParams->setAuthorized(false);
        $exportedSettings = $fileUploadParams->exportToArray();
        $this->assertEquals($exportedSettings['authorize'], false);

        $fileUploadParams->setAuthorized(true);
        $exportedSettings = $fileUploadParams->exportToArray();
        $this->assertEquals($exportedSettings['authorize'], false);
    }

    /**
     * @covers       \Smartling\File\FileApi::downloadFile
     *
     * @dataProvider downloadFileParams
     *
     * @param DownloadFileParameters|null $options
     * @param string $expected_translated_file
     */
    public function testDownloadFile($options, $locale, $expected_translated_file)
    {
        $this->prepareClientResponseMock(false);

        $this->responseMock->expects($this->any())
            ->method('getBody')
            ->willReturn($expected_translated_file);

        $endpointUrl = vsprintf(
            '%s/%s/locales/%s/file',
            [
                FileApi::ENDPOINT_URL,
                $this->projectId,
                $locale
            ]
        );

        $params = $options instanceof DownloadFileParameters
            ? $options->exportToArray()
            : [];

        $params['fileUri'] = 'test.xml';

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => $params,
            ])
            ->willReturn($this->responseMock);

        $actual_xml = $this->object->downloadFile('test.xml', $locale, $options);

        $this->assertEquals($expected_translated_file, $actual_xml);
    }

    public function downloadFileParams()
    {
        return [
            [
                (new DownloadFileParameters())->setRetrievalType(DownloadFileParameters::RETRIEVAL_TYPE_PSEUDO),
                'en-EN',
                '<?xml version="1.0"?><response><item key="6"></item></response>'
            ],
            [
                null,
                'en-EN',
                '<?xml version="1.0"?><response><item key="6"></item></response>'
            ],
            [
                null,
                'en',
                '{"string1":"translation1", "string2":"translation2"}'
            ],
        ];
    }

    /**
     * @covers       \Smartling\File\FileApi::downloadFile
     * @dataProvider downloadFileLocaleCheckSuccessParams
     * @expectedException Smartling\Exceptions\SmartlingApiException
     *
     * @param $options
     * @param $locale
     */
    public function testDownloadFileLocaleCheckFails($options, $locale)
    {
        $this->prepareClientResponseMock();
        $this->object->downloadFile('test.xml', $locale, $options);
    }

    public function downloadFileLocaleCheckSuccessParams()
    {
        return [
            [
                (new DownloadFileParameters())->setRetrievalType(DownloadFileParameters::RETRIEVAL_TYPE_PSEUDO),
                'e',
            ],
            [
                (new DownloadFileParameters())->setRetrievalType(DownloadFileParameters::RETRIEVAL_TYPE_PSEUDO),
                '',
            ],
            [
                (new DownloadFileParameters())->setRetrievalType(DownloadFileParameters::RETRIEVAL_TYPE_PSEUDO),
                [],
            ],
            [
                (new DownloadFileParameters())->setRetrievalType(DownloadFileParameters::RETRIEVAL_TYPE_PSEUDO),
                null,
            ],
            [
                (new DownloadFileParameters())->setRetrievalType(DownloadFileParameters::RETRIEVAL_TYPE_PSEUDO),
                (object)['foo'],
            ]
        ];
    }

    /**
     * @covers \Smartling\File\FileApi::lastModified
     * @expectedException \Smartling\Exceptions\SmartlingApiException
     * @expectedExceptionMessage No data found for file test.xml.
     */
    public function testLastModifiedInvalidResponseNoDataFoundNoItems()
    {
        $this->prepareClientResponseMock(false);

        $this->client->expects($this->once())
            ->method('request')
            ->willReturn($this->responseMock);

        $response = '{"response":{"code":"SUCCESS","messages":[], "data":{}}}';

        $this->responseMock->expects(self::any())
              ->method('getBody')
              ->willReturn($response);

        $this->object->lastModified('test.xml');
    }

    /**
     * @covers \Smartling\File\FileApi::lastModified
     * @expectedException \Smartling\Exceptions\SmartlingApiException
     * @expectedExceptionMessage No data found for file test.xml.
     */
    public function testLastModifiedInvalidResponseNoDataFoundItemsNotArray()
    {
        $this->prepareClientResponseMock(false);

        $this->client->expects($this->once())
            ->method('request')
            ->willReturn($this->responseMock);

        $response = '{"response":{"code":"SUCCESS","messages":[], "data":{"totalCount":1629,"items": "not_array"}}}';

        $this->responseMock->expects(self::any())
            ->method('getBody')
            ->willReturn($response);

        $this->object->lastModified('test.xml');
    }

    /**
     * @covers \Smartling\File\FileApi::lastModified
     * @expectedException \Smartling\Exceptions\SmartlingApiException
     * @expectedExceptionMessage Can't parse formatted time string: test.
     */
    public function testLastModifiedInvalidResponseCantParseFormattedTimeString()
    {
        $this->prepareClientResponseMock(false);

        $this->client->expects($this->once())
            ->method('request')
            ->willReturn($this->responseMock);

        $response = '{"response":{"code":"SUCCESS","messages":[], "data":{"totalCount":1629,"items": [{"localId": "locale-test","lastModified": "test"}]}}}';

        $this->responseMock->expects(self::any())
            ->method('getBody')
            ->willReturn($response);

        $this->object->lastModified('test.xml');
    }

    /**
     * @covers \Smartling\File\FileApi::lastModified
     * @expectedException \Smartling\Exceptions\SmartlingApiException
     * @expectedExceptionMessage Property "lastModified" is not found.
     */
    public function testLastModifiedInvalidResponseLastModifiedIsNotSet()
    {
        $this->prepareClientResponseMock(false);

        $this->client->expects($this->once())
            ->method('request')
            ->willReturn($this->responseMock);

        $response = '{"response":{"code":"SUCCESS","messages":[], "data":{"totalCount":1629,"items": [{"localId": "locale-test"}]}}}';

        $this->responseMock->expects(self::any())
            ->method('getBody')
            ->willReturn($response);

        $this->object->lastModified('test.xml');
    }

    /**
     * @covers \Smartling\File\FileApi::lastModified
     */
    public function testLastModifiedTimeZone()
    {
        $this->prepareClientResponseMock(false);

        $this->client->expects($this->exactly(2))
            ->method('request')
            ->willReturn($this->responseMock);

        $response = '{"response":{"code":"SUCCESS","messages":[], "data":{"totalCount":1629,"items": [{"localId": "locale-test","lastModified": "2018-01-01T00:00:00Z"}]}}}';

        $this->responseMock->expects(self::any())
            ->method('getBody')
            ->willReturn($response);

        date_default_timezone_set('UTC');
        $result_default_utc = $this->object->lastModified('test.xml');
        date_default_timezone_set('Pacific/Auckland');
        $result_default_auckland = $this->object->lastModified('test.xml');

        $this->assertEquals(
            $result_default_utc['items'][0]['lastModified']->getTimestamp(),
            $result_default_auckland['items'][0]['lastModified']->getTimestamp()
        );

        $this->assertEquals(
            $result_default_utc['items'][0]['lastModified']->getTimeZone()->getName(),
            'UTC'
        );

        $this->assertEquals(
            $result_default_auckland['items'][0]['lastModified']->getTimeZone()->getName(),
            'UTC'
        );
    }

    /**
     * @covers \Smartling\File\FileApi::lastModified
     */
    public function testLastModified()
    {
        $this->prepareClientResponseMock(false);
        $endpointUrl = vsprintf(
            '%s/%s/file/last-modified',
            [
                FileApi::ENDPOINT_URL,
                $this->projectId
            ]
        );

        $this->client->expects($this->once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [
                    'fileUri' => 'test.xml',
                ],
            ])
            ->willReturn($this->responseMock);

        $response = '{"response":{"code":"SUCCESS","messages":[], "data":{"totalCount":1629,"items": [{"localId": "locale-test","lastModified": "1970-01-01T00:00:00Z"}]}}}';

        $this->responseMock->expects(self::any())
            ->method('getBody')
            ->willReturn($response);

        $this->object->lastModified('test.xml');
    }

    /**
     * @covers \Smartling\File\FileApi::getStatusForAllLocales
     */
    public function testGetStatusForAllLocales()
    {
        $this->prepareClientResponseMock();
        $endpointUrl = vsprintf(
            '%s/%s/file/status',
            [
                FileApi::ENDPOINT_URL,
                $this->projectId
            ]
        );

        $this->client->expects($this->once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [
                    'fileUri' => 'test.xml',
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->getStatusForAllLocales('test.xml');
    }

    /**
     * @covers \Smartling\File\FileApi::getStatus
     */
    public function testGetStatus()
    {
        $this->prepareClientResponseMock();
        $endpointUrl = vsprintf(
            '%s/%s/locales/%s/file/status',
            [
                FileApi::ENDPOINT_URL,
                $this->projectId,
                'en-EN'
            ]
        );

        $this->client->expects($this->once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [
                    'fileUri' => 'test.xml',
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->getStatus('test.xml', 'en-EN');
    }

    /**
     * @covers \Smartling\File\FileApi::getList
     */
    public function testGetList()
    {
        $this->prepareClientResponseMock();
        $endpointUrl = vsprintf(
            '%s/%s/files/list',
            [
                FileApi::ENDPOINT_URL,
                $this->projectId
            ]
        );

        $this->client->expects($this->once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $this->object->getList();
    }


    /**
     * @covers \Smartling\File\FileApi::getExtendedList
     */
    public function testGetExtendedList()
    {
        $this->prepareClientResponseMock();
        $locale = 'nl-NL';
        $endpointUrl = vsprintf(
            '%s/%s/locales/%s/files/list',
            [
                FileApi::ENDPOINT_URL,
                $this->projectId,
                $locale
            ]
        );

        $this->client->expects($this->once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $this->object->getExtendedList($locale);
    }


    /**
     * @covers \Smartling\File\FileApi::sendRequest
     * @expectedException \Smartling\Exceptions\SmartlingApiException
     */
    public function testValidationErrorSendRequest()
    {
        $this->prepareClientResponseMock(false);

        $this->responseMock->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(400);
        $this->responseMock->expects($this->any())
            ->method('getBody')
            ->willReturn($this->responseWithException);

        $endpointUrl = vsprintf(
            '%s/%s/context/html',
            [
                FileApi::ENDPOINT_URL,
                $this->projectId
            ]
        );

        $this->client->expects($this->once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $requestData = $this->invokeMethod($this->object, 'getDefaultRequestData', ['query', []]);

        $this->invokeMethod($this->object, 'setBaseUrl', [FileApi::ENDPOINT_URL . '/' . $this->projectId]);
        $this->invokeMethod($this->object, 'sendRequest', ['context/html', $requestData, 'get']);
    }

    /**
     * @covers \Smartling\File\FileApi::sendRequest
     * @expectedException \Smartling\Exceptions\SmartlingApiException
     * @expectedExceptionMessage Bad response format from Smartling
     */
    public function testBadJsonFormatSendRequest()
    {
        $this->prepareClientResponseMock(false);

        $this->responseMock->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(400);
        $this->responseMock->expects($this->any())
            ->method('getBody')
            ->willReturn(rtrim($this->responseWithException, '}'));

        $endpointUrl = vsprintf(
            '%s/%s/context/html',
            [
                FileApi::ENDPOINT_URL,
                $this->projectId
            ]
        );

        $this->client->expects($this->once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $requestData = $this->invokeMethod($this->object, 'getDefaultRequestData', ['query', []]);

        $this->invokeMethod($this->object, 'setBaseUrl', [FileApi::ENDPOINT_URL . '/' . $this->projectId]);
        $this->invokeMethod($this->object, 'sendRequest', ['context/html', $requestData, 'get']);
    }

    /**
     * @covers \Smartling\File\FileApi::sendRequest
     * @expectedException \Smartling\Exceptions\SmartlingApiException
     * @expectedExceptionMessage Bad response format from Smartling
     */
    public function testBadJsonFormatInErrorMessageSendRequest()
    {
        $this->prepareClientResponseMock(false);

        $this->responseMock->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(401);
        $this->responseMock->expects($this->any())
            ->method('getBody')
            ->willReturn(rtrim($this->responseWithException, '}'));

        $endpointUrl = vsprintf(
            '%s/%s/context/html',
            [
                FileApi::ENDPOINT_URL,
                $this->projectId
            ]
        );

        $this->client->expects($this->once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $requestData = $this->invokeMethod($this->object, 'getDefaultRequestData', ['query', []]);

        $this->invokeMethod($this->object, 'setBaseUrl', [FileApi::ENDPOINT_URL . '/' . $this->projectId]);
        $this->invokeMethod($this->object, 'sendRequest', ['context/html', $requestData, 'get']);
    }

    /**
     * @param string $uri
     * @param array $requestData
     * @param string $method
     * @param array $params
     * @param $paramsType
     * @covers       \Smartling\File\FileApi::sendRequest
     * @dataProvider sendRequestValidProvider
     */
    public function testSendRequest($uri, $requestData, $method, $params, $paramsType)
    {
        $this->prepareClientResponseMock();
        $defaultRequestData = $this->invokeMethod($this->object, 'getDefaultRequestData', [$paramsType, $requestData]);

        $params['headers']['Authorization'] = vsprintf('%s %s', [
            $this->authProvider->getTokenType(),
            $this->authProvider->getAccessToken(),
        ]);

        $this->client->expects($this->once())
            ->method('request')
            ->with($method, FileApi::ENDPOINT_URL . '/' . $this->projectId . '/' . $uri, $params)
            ->willReturn($this->responseMock);

        $this->invokeMethod($this->object, 'setBaseUrl', [FileApi::ENDPOINT_URL . '/' . $this->projectId]);

        $result = $this->invokeMethod($this->object, 'sendRequest', [$uri, $defaultRequestData, $method]);
        self::assertEquals(['wordCount' => 1629, 'stringCount' => 503, 'overWritten' => false], $result);
    }

    /**
     * Data provider callback for testSendRequest method.
     *
     * @return array
     */
    public function sendRequestValidProvider()
    {
        return [
            [
                'uri',
                [],
                'get',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'exceptions' => false,
                    'query' => [],
                ],
                'query',
            ],
            [
                'uri',
                [
                    'key' => 'value',
                    'boolean_false' => false,
                    'boolean_true' => true,
                    'file' => './tests/resources/test.xml',
                ],
                'post',
                [
                    'headers' => [
                        'Accept' => 'application/json',
                    ],
                    'exceptions' => false,
                    'multipart' => [
                        [
                            'name' => 'key',
                            'contents' => 'value',
                        ],
                      [
                        'name' => 'boolean_false',
                        'contents' => '0',
                      ],
                      [
                        'name' => 'boolean_true',
                        'contents' => '1',
                      ],
                      [
                        'name' => 'file',
                        'contents' => $this->streamPlaceholder,
                      ],
                    ],
                ],
                'multipart',
            ],
        ];
    }

    /**
     * @covers \Smartling\File\FileApi::renameFile
     */
    public function testRenameFile()
    {
        $this->prepareClientResponseMock();
        $endpointUrl = vsprintf(
            '%s/%s/file/rename',
            [
                FileApi::ENDPOINT_URL,
                $this->projectId
            ]
        );

        $this->client->expects($this->once())
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
                'form_params' => [
                    'fileUri' => 'test.xml',
                    'newFileUri' => 'new_test.xml',
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->renameFile('test.xml', 'new_test.xml');
    }

    /**
     * @covers \Smartling\File\FileApi::getAuthorizedLocales
     */
    public function testGetAuthorizedLocales()
    {
        $this->prepareClientResponseMock();
        $endpointUrl = vsprintf(
            '%s/%s/file/authorized-locales',
            [
                FileApi::ENDPOINT_URL,
                $this->projectId
            ]
        );

        $this->client->expects($this->once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [
                    'fileUri' => 'test.xml',
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->getAuthorizedLocales('test.xml');
    }

    /**
     * @covers \Smartling\File\FileApi::deleteFile
     */
    public function testDeleteFile()
    {
        $this->prepareClientResponseMock();
        $endpointUrl = vsprintf('%s/%s/file/delete', [FileApi::ENDPOINT_URL, $this->projectId]);

        $this->client->expects($this->once())
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
                'form_params' => [
                    'fileUri' => 'test.xml',
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->deleteFile('test.xml');
    }

    /**
     * @covers \Smartling\File\FileApi::import
     */
    public function testImport()
    {
        $this->prepareClientResponseMock();
        $locale = 'en-EN';
        $endpointUrl = vsprintf(
            '%s/%s/locales/%s/file/import',
            [
                FileApi::ENDPOINT_URL,
                $this->projectId,
                $locale
            ]
        );

        $this->client->expects($this->once())
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
                'multipart' => [
                    [
                        'name' => 'fileUri',
                        'contents' => 'test.xml',
                    ],
                    [
                        'name' => 'fileType',
                        'contents' => 'xml',
                    ],
                    [
                        'name' => 'file',
                        'contents' => $this->streamPlaceholder,
                    ],
                    [
                        'name' => 'translationState',
                        'contents' => 'PUBLISHED',
                    ],
                    [
                        'name' => 'overwrite',
                        'contents' => '0',
                    ],
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->import(
            $locale,
            'test.xml',
            'xml',
            'tests/resources/test.xml',
            'PUBLISHED',
            false
        );
    }

    /**
     * @covers \Smartling\File\FileApi::readFile
     */
    public function testReadFile()
    {
        $this->prepareClientResponseMock();
        $validFilePath = './tests/resources/test.xml';

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|\Smartling\File\FileApi
         */
        $fileApi = $this->getMockBuilder('Smartling\\File\\FileApi')
            ->setConstructorArgs([$this->projectId, $this->client])
            ->getMock();

        $stream = $this->invokeMethod($fileApi, 'readFile', [$validFilePath]);

        $this->assertEquals('stream', get_resource_type($stream));
    }

    /**
     * @covers \Smartling\File\FileApi::readFile
     *
     * @expectedException \Smartling\Exceptions\SmartlingApiException
     * @expectedExceptionMessage File unexisted was not able to be read.
     */
    public function testFailedReadFile()
    {
        $this->prepareClientResponseMock();
        $invalidFilePath = 'unexisted';

        /**
         * @var \PHPUnit_Framework_MockObject_MockObject|\Smartling\File\FileApi
         */
        $fileApi = $this->getMockBuilder('Smartling\\File\\FileApi')
            ->setConstructorArgs([$this->projectId, $this->client])
            ->getMock();

        $stream = $this->invokeMethod($fileApi, 'readFile', [$invalidFilePath]);

        $this->assertEquals('stream', get_resource_type($stream));
    }

    /**
     * Test async response with ACCEPTED code.
     *
     * It should not throw "Bad response format" exception.
     */
    public function testAcceptResponse() {
        $this->prepareClientResponseMock();
        $responseMock = $this->getMockBuilder('Guzzle\Message\ResponseInterface')
            ->setMethods(
                array_merge(
                    self::$responseInterfaceMethods,
                    self::$messageInterfaceMethods
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $responseMock->expects(self::any())
            ->method('getStatusCode')
            ->willReturn(202);

        $responseMock->expects(self::any())
            ->method('getBody')
            ->willReturn($this->responseAsync);

        $responseMock->expects(self::any())
            ->method('json')
            ->willReturn(
                json_decode($this->responseAsync, true)
            );

        $this->client->expects(self::once())
            ->method('request')
            ->willReturn($responseMock);

        // Just random api call to mock async response of 'send' method.
        $this->object->renameFile('test.xml', 'new_test.xml');
    }

}
