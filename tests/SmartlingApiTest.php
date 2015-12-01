<?php

namespace Smartling\Tests;

use Smartling\SmartlingApi;

/**
 * Test class for SmartlingAPI.
 */
class SmartlingApiTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Smartling\SmartlingApi
     */
    protected $object;

    /**
     * @var string
     */
    protected $apiKey = 'TEST_API_KEY';

    /**
     * @var string
     */
    protected $projectId = 'TEST_PROJECT_ID';

    /**
     * @var string
     */
    protected $validResponse = '{"response":{"data":{"wordCount":1629,"stringCount":503,"overWritten":false},"code":"SUCCESS","messages":[]}}';

    /**
     * @var string
     */
    protected $responseWithException = '{"response":{"data":null,"code":"VALIDATION_ERROR","messages":["Validation error text"]}}';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\GuzzleHttp\ClientInterface
     */
    protected $client;

    /**
     * @var string
     */
    protected $streamPlaceholder = 'stream';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|\Psr\Http\Message\StreamInterface
     */
    protected $responseMock;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->client = $this->getMockBuilder('GuzzleHttp\\ClientInterface')
            ->setMethods(['request', 'send', 'sendAsync', 'requestAsync', 'getConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock = $this->getMockBuilder('Psr\\Http\\Message\\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock->expects($this->any())
            ->method('getBody')
            ->willReturn($this->validResponse);
        $this->object = $this->getMockBuilder('Smartling\\SmartlingApi')
            ->setMethods(['readFile'])
            ->setConstructorArgs([SmartlingApi::SANDBOX_URL, $this->apiKey, $this->projectId, $this->client, SmartlingApi::SANDBOX_MODE])
            ->getMock();
        $this->object->expects($this->any())
            ->method('readFile')
            ->willReturn($this->streamPlaceholder);
    }

    /**
     * Invokes protected or private method of given object.
     *
     * @param object $object
     *   Object with protected or private method to invoke.
     * @param string $methodName
     *   Name of the property to invoke.
     * @param array $parameters
     *   Array of parameters to be passed to invoking method.
     *
     * @return mixed
     *   Value invoked method will return or exception.
     */
    protected function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Reads protected or private property of given object.
     *
     * @param object $object
     *   Object with protected or private property.
     * @param string $propertyName
     *   Name of the property to access.
     *
     * @return mixed
     *   Value of read property.
     */
    protected function readProperty($object, $propertyName)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);

        return $property->getValue($object);
    }

    /**
     * Tests constructor.
     *
     * @param string $actualBaseUrl
     *   Base Url string that will be passed as and argument to constructor.
     * @param string $apiKey
     *   Api key string.
     * @param string $projectId
     *   Project Id string.
     * @param \GuzzleHttp\ClientInterface $client
     *   Mock of Guzzle http client instance.
     * @param null|string $mode
     *   Production, sandbox or no value for mode.
     * @param string $expectedBaseUrl
     *   Base url that has to be set inside constructor.
     *
     * @covers       \Smartling\SmartlingApi::__construct
     *
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($actualBaseUrl, $apiKey, $projectId, $client, $mode = null, $expectedBaseUrl = '')
    {
        $smartlingApi = new SmartlingApi($actualBaseUrl, $apiKey, $projectId, $client, $mode);

        $this->assertEquals($expectedBaseUrl, $this->readProperty($smartlingApi, 'baseUrl'));
        $this->assertEquals($apiKey, $this->readProperty($smartlingApi, 'apiKey'));
        $this->assertEquals($projectId, $this->readProperty($smartlingApi, 'projectId'));
        $this->assertEquals($client, $this->readProperty($smartlingApi, 'httpClient'));
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
        $mockedClient = $this->getMockBuilder('GuzzleHttp\\ClientInterface')
            ->setMethods(['request', 'send', 'sendAsync', 'requestAsync', 'getConfig'])
            ->disableOriginalConstructor()
            ->getMock();

        return [
            ['uri', 'api-key', 'product-id', $mockedClient, SmartlingApi::SANDBOX_MODE, rtrim(SmartlingApi::SANDBOX_URL, '/')],
            ['uri', 'api-key', 'product-id', $mockedClient, SmartlingApi::PRODUCTION_MODE, 'uri'],
            ['uri', 'api-key', 'product-id', $mockedClient, null, rtrim(SmartlingApi::SANDBOX_URL, '/')],
            ['uri', 'api-key', 'product-id', $mockedClient, 'unknown', rtrim(SmartlingApi::SANDBOX_URL, '/')],
            ['', 'api-key', 'product-id', $mockedClient, 'unknown', rtrim(SmartlingApi::SANDBOX_URL, '/')],
            ['', 'api-key', 'product-id', $mockedClient, SmartlingApi::PRODUCTION_MODE, rtrim(SmartlingApi::PRODUCTION_URL, '/')],
            ['', 'api-key', 'product-id', $mockedClient, SmartlingApi::SANDBOX_MODE, rtrim(SmartlingApi::SANDBOX_URL, '/')],
            ['', 'api-key', 'product-id', $mockedClient, null, rtrim(SmartlingApi::SANDBOX_URL, '/')],
        ];
    }

    /**
     * @covers \Smartling\SmartlingApi::uploadFile
     */
    public function testUploadFile()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', SmartlingApi::SANDBOX_URL . 'file/upload', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $this->streamPlaceholder,
                    ],
                    [
                        'name' => 'approved',
                        'contents' => '1'
                    ],
                    [
                        'name' => 'fileUri',
                        'contents' => 'test.xml'
                    ],
                    [
                        'name' => 'fileType',
                        'contents' => 'xml'
                    ],
                    [
                        'name' => 'apiKey',
                        'contents' => $this->apiKey,
                    ],
                    [
                        'name' => 'projectId',
                        'contents' => $this->projectId,
                    ],
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->uploadFile('tests/resources/test.xml', 'test.xml', 'xml', ['approved' => TRUE]);
    }

    /**
     * @covers \Smartling\SmartlingApi::downloadFile
     */
    public function testDownloadFile()
    {
        $expected_xml = '<?xml version="1.0"?><response><item key="6"></item></response>';
        $this->responseMock = $this->getMockBuilder('Psr\\Http\\Message\\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock->expects($this->any())
            ->method('getBody')
            ->willReturn($expected_xml);

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', SmartlingApi::SANDBOX_URL . 'file/get', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'query' => [
                    'retrievalType' => 'pseudo',
                    'fileUri' => 'test.xml',
                    'locale' => 'en-EN',
                    'apiKey' => $this->apiKey,
                    'projectId' => $this->projectId,
                ],
            ])
            ->willReturn($this->responseMock);

        $actual_xml = $this->object->downloadFile('test.xml', 'en-EN', ['retrievalType' => 'pseudo']);

        $this->assertEquals($expected_xml, $actual_xml);
    }

    /**
     * @covers \Smartling\SmartlingApi::getStatus
     */
    public function testGetStatus()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', SmartlingApi::SANDBOX_URL . 'file/status', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'query' => [
                    'fileUri' => 'test.xml',
                    'locale' => 'en-EN',
                    'apiKey' => $this->apiKey,
                    'projectId' => $this->projectId,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->getStatus('test.xml', 'en-EN');
    }

    /**
     * @covers \Smartling\SmartlingApi::getLocaleList
     */
    public function testGetLocaleList()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', SmartlingApi::SANDBOX_URL . 'project/locale/list', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'query' => [
                    'apiKey' => $this->apiKey,
                    'projectId' => $this->projectId,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->getLocaleList();
    }


    /**
     * @covers \Smartling\SmartlingApi::getList
     */
    public function testGetList()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', SmartlingApi::SANDBOX_URL . 'file/list', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'query' => [
                    'locale' => 'en-EN',
                    'apiKey' => $this->apiKey,
                    'projectId' => $this->projectId,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->getList('en-EN');
    }

    /**
     * @covers \Smartling\SmartlingApi::renameFile
     */
    public function testRenameFile()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', SmartlingApi::SANDBOX_URL . 'file/rename', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'multipart' => [
                    [
                        'name' => 'fileUri',
                        'contents' => 'test.xml'
                    ],
                    [
                        'name' => 'newFileUri',
                        'contents' => 'new_test.xml'
                    ],
                    [
                        'name' => 'apiKey',
                        'contents' => $this->apiKey,
                    ],
                    [
                        'name' => 'projectId',
                        'contents' => $this->projectId,
                    ],
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->renameFile('test.xml', 'new_test.xml');
    }

    /**
     * @covers \Smartling\SmartlingApi::getAuthorizedLocales
     */
    public function testGetAuthorizedLocales()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', SmartlingApi::SANDBOX_URL . 'file/authorized_locales', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'query' => [
                    'fileUri' => 'test.xml',
                    'apiKey' => $this->apiKey,
                    'projectId' => $this->projectId,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->getAuthorizedLocales('test.xml');
    }

    /**
     * @covers \Smartling\SmartlingApi::import
     */
    public function testImport()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', SmartlingApi::SANDBOX_URL . 'file/import', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $this->streamPlaceholder,
                    ],
                    [
                        'name' => 'overwrite',
                        'contents' => '0',
                    ],
                    [
                        'name' => 'fileUri',
                        'contents' => 'test.xml'
                    ],
                    [
                        'name' => 'fileType',
                        'contents' => 'xml'
                    ],
                    [
                        'name' => 'locale',
                        'contents' => 'en-EN'
                    ],
                    [
                        'name' => 'translationState',
                        'contents' => 'PUBLISHED'
                    ],
                    [
                        'name' => 'apiKey',
                        'contents' => $this->apiKey,
                    ],
                    [
                        'name' => 'projectId',
                        'contents' => $this->projectId,
                    ],
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->import('test.xml', 'xml', 'en-EN', 'tests/resources/test.xml', 'PUBLISHED', ['overwrite' => FALSE]);
    }

    /**
     * @covers \Smartling\SmartlingApi::deleteFile
     */
    public function testDeleteFile()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('DELETE', SmartlingApi::SANDBOX_URL . 'file/delete', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'query' => [
                    'fileUri' => 'test.xml',
                    'apiKey' => $this->apiKey,
                    'projectId' => $this->projectId,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->deleteFile('test.xml');
    }

    /**
     * @covers \Smartling\SmartlingApi::getContextStats
     */
    public function testGetContextStats()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', SmartlingApi::SANDBOX_URL . 'context/html', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'query' => [
                    'apiKey' => $this->apiKey,
                    'projectId' => $this->projectId,
                ],
            ])
            ->willReturn($this->responseMock);
        $this->object->getContextStats([]);
    }

    /**
     * @covers \Smartling\SmartlingApi::uploadContext
     */
    public function testUploadContext()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', SmartlingApi::SANDBOX_URL . 'context/html', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'multipart' => [
                    [
                        'name' => 'apiKey',
                        'contents' => $this->apiKey,
                    ],
                    [
                        'name' => 'projectId',
                        'contents' => $this->projectId,
                    ]
                ],
            ])
            ->willReturn($this->responseMock);
        $this->object->uploadContext([]);
    }

    /**
     * @covers \Smartling\SmartlingApi::sendRequest
     * @expectedException \Smartling\SmartlingApiException
     * @expectedExceptionMessage Validation error text
     */
    public function testValidationErrorSendRequest()
    {
        $response = $this->getMockBuilder('Psr\\Http\\Message\\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->any())
            ->method('getBody')
            ->willReturn($this->responseWithException);

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', SmartlingApi::SANDBOX_URL . 'context/html', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'query' => [
                    'apiKey' => $this->apiKey,
                    'projectId' => $this->projectId,
                ],
            ])
            ->willReturn($response);

        $this->invokeMethod($this->object, 'sendRequest', ['context/html', [], 'GET']);
    }

    /**
     * @param string $uri
     * @param array $requestData
     * @param string $method
     * @param array $params
     *
     * @covers       \Smartling\SmartlingApi::sendRequest
     * @dataProvider sendRequestValidProvider
     */
    public function testSendRequest($uri, $requestData, $method, $params)
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with($method, SmartlingApi::SANDBOX_URL . $uri, $params)
            ->willReturn($this->responseMock);

        $result = $this->invokeMethod($this->object, 'sendRequest', [$uri, $requestData, $method]);
        $this->assertEquals(['wordCount' => 1629, 'stringCount' => 503, 'overWritten' => false], $result);
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
                'GET',
                [
                    'headers' => ['Accept' => 'application/json'],
                    'http_errors' => FALSE,
                    'query' => [
                        'apiKey' => $this->apiKey,
                        'projectId' => $this->projectId,
                    ],
                ]
            ],
            [
                'uri',
                [
                    'key' => 'value',
                    'boolean_false' => FALSE,
                    'boolean_true' => TRUE,
                    'file' => './tests/resources/test.xml'
                ],
                'POST',
                [
                    'headers' => ['Accept' => 'application/json'],
                    'http_errors' => FALSE,
                    'multipart' => [
                        [
                            'name' => 'file',
                            'contents' => $this->streamPlaceholder,
                        ],
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
                            'name' => 'apiKey',
                            'contents' => $this->apiKey,
                        ],
                        [
                            'name' => 'projectId',
                            'contents' => $this->projectId,
                        ]
                    ],
                ]
            ]
        ];
    }

    /**
     * @covers \Smartling\SmartlingApi::readFile
     */
    public function testReadFile()
    {
        $validFilePath = './tests/resources/test.xml';
        $smartlingApi = $this->getMockBuilder('Smartling\\SmartlingApi')
            ->setConstructorArgs([SmartlingApi::SANDBOX_URL, $this->apiKey, $this->projectId, $this->client, SmartlingApi::SANDBOX_MODE])
            ->getMock();

        $stream = $this->invokeMethod($smartlingApi, 'readFile', [$validFilePath]);

        $this->assertEquals('stream', get_resource_type($stream));
    }

    /**
     * @covers \Smartling\SmartlingApi::readFile
     *
     * @expectedException \Smartling\SmartlingApiException
     * @expectedExceptionMessage File unexisted was not able to be read.
     */
    public function testFailedreadFile()
    {
        $invalidFilePath = 'unexisted';
        $smartlingApi = $this->getMockBuilder('Smartling\\SmartlingApi')
            ->setConstructorArgs([SmartlingApi::SANDBOX_URL, $this->apiKey, $this->projectId, $this->client, SmartlingApi::SANDBOX_MODE])
            ->getMock();

        $this->invokeMethod($smartlingApi, 'readFile', [$invalidFilePath]);
    }
}
