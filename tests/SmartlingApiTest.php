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
            ->setConstructorArgs([$this->apiKey, $this->projectId, $this->client])
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
     * @param string $apiKey
     *   Api key string.
     * @param string $projectId
     *   Project Id string.
     * @param \GuzzleHttp\ClientInterface $client
     *   Mock of Guzzle http client instance.
     * @param string|null $expected_base_url
     *   Base Url string that will be used as based url.
     * @param string|null $actual_base_url
     *   Base Url string that will be passed as and argument to constructor.
     *
     * @covers       \Smartling\SmartlingApi::__construct
     *
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($apiKey, $projectId, $client, $expected_base_url, $actual_base_url)
    {
        $smartlingApi = new SmartlingApi($apiKey, $projectId, $client, $actual_base_url);

        $this->assertEquals($expected_base_url, $this->readProperty($smartlingApi, 'baseUrl'));
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
            ['api-key', 'product-id', $mockedClient, SmartlingApi::DEFAULT_SERVICE_URL, null],
            ['api-key', 'product-id', $mockedClient, SmartlingApi::DEFAULT_SERVICE_URL, SmartlingApi::DEFAULT_SERVICE_URL],
            ['api-key', 'product-id', $mockedClient, SmartlingApi::DEFAULT_SERVICE_URL, SmartlingApi::DEFAULT_SERVICE_URL . '/'],
            ['api-key', 'product-id', $mockedClient, 'https://www.google.com.ua/webhp', 'https://www.google.com.ua/webhp'],
        ];
    }

    /**
     * @covers \Smartling\SmartlingApi::uploadFile
     */
    public function testUploadFile()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', SmartlingApi::DEFAULT_SERVICE_URL . '/file/upload', [
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
     *
     * @dataProvider downloadFileParams
     */
    public function testDownloadFile($options, $expected_translated_file)
    {
        $this->responseMock = $this->getMockBuilder('Psr\\Http\\Message\\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->responseMock->expects($this->any())
            ->method('getBody')
            ->willReturn($expected_translated_file);

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', SmartlingApi::DEFAULT_SERVICE_URL . '/file/get', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'query' => $options + [
                    'fileUri' => 'test.xml',
                    'locale' => 'en-EN',
                    'apiKey' => $this->apiKey,
                    'projectId' => $this->projectId,
                ],
            ])
            ->willReturn($this->responseMock);

        $actual_xml = $this->object->downloadFile('test.xml', 'en-EN', $options);

        $this->assertEquals($expected_translated_file, $actual_xml);
    }

    public function downloadFileParams() {
        return [
            [['retrievalType' => 'pseudo'], '<?xml version="1.0"?><response><item key="6"></item></response>'],
            [[], '<?xml version="1.0"?><response><item key="6"></item></response>'],
            [[], '{"string1":"translation1", "string2":"translation2"}'],
        ];
    }

    /**
     * @covers \Smartling\SmartlingApi::getStatus
     */
    public function testGetStatus()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', SmartlingApi::DEFAULT_SERVICE_URL . '/file/status', [
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
            ->with('GET', SmartlingApi::DEFAULT_SERVICE_URL . '/project/locale/list', [
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
            ->with('GET', SmartlingApi::DEFAULT_SERVICE_URL . '/file/list', [
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
            ->with('POST', SmartlingApi::DEFAULT_SERVICE_URL . '/file/rename', [
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
            ->with('GET', SmartlingApi::DEFAULT_SERVICE_URL . '/file/authorized_locales', [
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
            ->with('POST', SmartlingApi::DEFAULT_SERVICE_URL . '/file/import', [
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
            ->with('DELETE', SmartlingApi::DEFAULT_SERVICE_URL . '/file/delete', [
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
            ->with('GET', SmartlingApi::DEFAULT_SERVICE_URL . '/context/html', [
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
            ->with('POST', SmartlingApi::DEFAULT_SERVICE_URL . '/context/html', [
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
            ->method('getStatusCode')
            ->willReturn(400);
        $response->expects($this->any())
            ->method('getBody')
            ->willReturn($this->responseWithException);

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', SmartlingApi::DEFAULT_SERVICE_URL . '/context/html', [
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
     * @covers \Smartling\SmartlingApi::sendRequest
     * @expectedException \Smartling\SmartlingApiException
     * @expectedExceptionMessage Bad response format from Smartling
     */
    public function testBadJsonFormatSendRequest() {
        $response = $this->getMockBuilder('Psr\\Http\\Message\\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(200);
        $response->expects($this->any())
            ->method('getBody')
            ->willReturn(rtrim($this->responseWithException, '}'));

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', SmartlingApi::DEFAULT_SERVICE_URL . '/context/html', [
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
     * @covers \Smartling\SmartlingApi::sendRequest
     * @expectedException \Smartling\SmartlingApiException
     * @expectedExceptionMessage Bad response format from Smartling
     */
    public function testBadJsonFormatInErrorMessageSendRequest() {
        $response = $this->getMockBuilder('Psr\\Http\\Message\\ResponseInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $response->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(401);
        $response->expects($this->any())
            ->method('getBody')
            ->willReturn(rtrim($this->responseWithException, '}'));

        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', SmartlingApi::DEFAULT_SERVICE_URL . '/context/html', [
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
            ->with($method, SmartlingApi::DEFAULT_SERVICE_URL . '/' .$uri, $params)
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
            ->setConstructorArgs([$this->apiKey, $this->projectId, $this->client])
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
    public function testFailedReadFile()
    {
        $invalidFilePath = 'unexisted';
        $smartlingApi = $this->getMockBuilder('Smartling\\SmartlingApi')
            ->setConstructorArgs([$this->apiKey, $this->projectId, $this->client])
            ->getMock();

        $this->invokeMethod($smartlingApi, 'readFile', [$invalidFilePath]);
    }
}
