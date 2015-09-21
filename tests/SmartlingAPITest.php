<?php

namespace Smartling\Tests;

use Smartling\SmartlingApi;

/**
 * Test class for SmartlingAPI.
 */
class SmartlingAPITest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var SmartlingAPI
     */
    protected $object;
    protected $apiKey = 'TEST_API_KEY';
    protected $projectId = 'TEST_PROJECT_ID';
    protected $validResponse = '{"response":{"data":{"wordCount":1629,"stringCount":503,"overWritten":false},"code":"SUCCESS","messages":[]}}';
    protected $client;
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

        $this->object = new SmartlingApi(SmartlingApi::SANDBOX_URL, $this->apiKey, $this->projectId, $this->client, SmartlingApi::SANDBOX_MODE);
    }

    /**
     *
     * @param object $object
     * @param string $methodName
     * @param array $parameters
     * @return string | null | int | object | bool | resource | float
     */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * @covers \Smartling\SmartlingApi::uploadFile
     */
    public function testUploadFile()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('POST', 'file/upload', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => fopen('test/resources/test.xml', 'r')
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

        $this->object->uploadFile('test/resources/test.xml', 'test.xml', 'xml', ['approved' => TRUE]);
    }

    /**
     * @covers \Smartling\SmartlingApi::downloadFile
     */
    public function testDownloadFile()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'file/get', [
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

        $this->object->downloadFile('test.xml', 'en-EN', ['retrievalType' => 'pseudo']);
    }

    /**
     * @covers \Smartling\SmartlingApi::getStatus
     */
    public function testGetStatus()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'file/status', [
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
     * @covers \Smartling\SmartlingApi::getList
     */
    public function testGetList()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('GET', 'file/list', [
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
            ->with('POST', 'file/rename', [
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
            ->with('GET', 'file/authorized_locales', [
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
            ->with('POST', 'file/import', [
                'headers' => ['Accept' => 'application/json'],
                'http_errors' => FALSE,
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => '0',
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

        $this->object->import('test.xml', 'xml', 'en-EN', 'test/resources/test.xml', 'PUBLISHED', ['overwrite' => FALSE]);
    }

    /**
     * @covers \Smartling\SmartlingApi::deleteFile
     */
    public function testDeleteFile()
    {
        $this->client->expects($this->once())
            ->method('request')
            ->with('DELETE', 'file/delete', [
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
     * @covers \Smartling\SmartlingApi::sendRequest
     */
    public function _testSendRequest()
    {

        //check response type
        $this->assertInternalType(
            'string',
            $this->invokeMethod($this->object, 'sendRequest', array(
                '',
                array(),
                'POST'
            ))
        );

        //check not equals false
        $this->assertNotEquals(
            false,
            $this->invokeMethod($this->object, 'sendRequest', array(
                '',
                array(),
                'POST'
            ))
        );

    }
}
