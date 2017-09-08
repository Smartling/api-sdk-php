<?php

namespace Smartling\Tests;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use PHPUnit_Framework_MockObject_MockObject;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;

/**
 * Class ApiTestAbstract
 * @package Smartling\Tests
 */
abstract class ApiTestAbstract extends \PHPUnit_Framework_TestCase
{
    const JSON_OBJECT_AS_ARRAY = 1;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|BaseApiAbstract
     */
    protected $object;

    /**
     * @var string
     */
    protected $userIdentifier = 'SomeUserIdentifier';

    /**
     * @var string
     */
    protected $secretKey = 'SomeSecretKey';

    /**
     * @var string
     */
    protected $projectId = 'SomeProjectId';

    /**
     * @var string
     */
    protected $validResponse = '{"response":{"data":{"wordCount":1629,"stringCount":503,"overWritten":false},"code":"SUCCESS","messages":[]}}';

    /**
     * @var string
     */
    protected $responseWithException = '{"response":{"data":null,"code":"VALIDATION_ERROR","errors":[{"message":"Validation error text"}]}}';

    /**
     * @var string
     */
    protected $responseAsync = '{"response":{"data":{"message":"message", "url":"url"},"code":"ACCEPTED"}}';

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ClientInterface
     */
    protected $client;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|AuthApiInterface
     */
    protected $authProvider;

    /**
     * @var string
     */
    protected $streamPlaceholder = 'stream';

    /**
     * @var PHPUnit_Framework_MockObject_MockObject|ResponseInterface
     */
    protected $responseMock;

    /**
     * @var RequestInterface | PHPUnit_Framework_MockObject_MockObject
     */
    protected $requestMock;

    protected static $requestInterfaceMethods = [
        'setUrl',
        'getUrl',
        'getResource',
        'getQuery',
        'setQuery',
        'getMethod',
        'setMethod',
        'getScheme',
        'setScheme',
        'getPort',
        'setPort',
        'getHost',
        'setHost',
        'getPath',
        'setPath',
        'getConfig'
    ];

    protected static $hasEmitterInterfaceMethods = [
        'getEmitter'
    ];

    protected static $messageInterfaceMethods = [
        '__toString',
        'getProtocolVersion',
        'setBody',
        'getBody',
        'getHeaders',
        'getHeader',
        'getHeaderAsArray',
        'hasHeader',
        'removeHeader',
        'addHeader',
        'addHeaders',
        'setHeader',
        'setHeaders'
    ];

    protected static $clientInterfaceMethods = [
        'createRequest',
        'get',
        'head',
        'delete',
        'put',
        'patch',
        'post',
        'options',
        'send',
        'getDefaultOption',
        'setDefaultOption',
        'getBaseUrl'
    ];

    protected static $responseInterfaceMethods = [
        'getStatusCode',
        'setStatusCode',
        'getReasonPhrase',
        'setReasonPhrase',
        'getEffectiveUrl',
        'setEffectiveUrl',
        'json',
        'xml'
    ];

    /**
     * Invokes protected or private method of given object.
     *
     * @param BaseApiAbstract $object
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
     * @param BaseApiAbstract $object
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

    protected function prepareHttpClientMock()
    {
        $this->requestMock = $this->getMockBuilder('GuzzleHttp\Message\RequestInterface')
            ->setMethods(
                array_merge(
                    self::$requestInterfaceMethods,
                    self::$hasEmitterInterfaceMethods,
                    self::$messageInterfaceMethods
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $this->client = $this->getMockBuilder('GuzzleHttp\ClientInterface')
            ->setMethods(
                array_merge(
                    self::$clientInterfaceMethods,
                    self::$hasEmitterInterfaceMethods
                )
            )
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function prepareAuthProviderMock()
    {
        $this->authProvider = $this->getMockBuilder('\Smartling\AuthApi\AuthApiInterface')
            ->setMethods(
                [
                    'getAccessToken',
                    'getTokenType',
                    'resetToken'
                ]
            )
            ->setConstructorArgs([$this->userIdentifier, $this->secretKey, $this->client])
            ->getMock();

        $this->authProvider->expects(self::any())->method('getAccessToken')->willReturn('fakeToken');
        $this->authProvider->expects(self::any())->method('getTokenType')->willReturn('Bearer');
        $this->authProvider->expects(self::any())->method('resetToken');
    }

    protected function prepareClientResponseMock($setDefaultResponse = true)
    {
        $this->responseMock = $this->getMockBuilder('Guzzle\Message\ResponseInterface')
            ->setMethods(
                array_merge(
                    self::$responseInterfaceMethods,
                    self::$messageInterfaceMethods
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        if (true === $setDefaultResponse) {
            $this->responseMock
                ->expects(self::any())
                ->method('json')
                ->willReturn(
                    json_decode(
                        $this->validResponse,
                        self::JSON_OBJECT_AS_ARRAY
                    )
                );

            $this->responseMock->expects(self::any())
                ->method('getBody')
                ->willReturn($this->validResponse);
        }

        $this->responseMock->expects(self::any())
            ->method('getStatusCode')
            ->willReturn(200);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->prepareHttpClientMock();
        $this->prepareAuthProviderMock();
        $this->prepareClientResponseMock();
    }
}