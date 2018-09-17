<?php

namespace Smartling\Tests;

use Smartling\AuthApi\AuthTokenProvider;
use Smartling\Tests\Unit\ApiTestAbstract;

/**
 * Test class for Smartling\AuthApi\AuthTokenProvider.
 */
class AuthApiTest extends ApiTestAbstract
{

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->prepareAuthApiMock();
    }

    private function prepareAuthApiMock()
    {
        $this->object = $this->getMockBuilder('Smartling\AuthApi\AuthTokenProvider')
            ->setMethods(NULL)
            ->setConstructorArgs([
                $this->userIdentifier,
                $this->secretKey,
                $this->client,
            ])
            ->getMock();
    }

    /**
     * Test auth method.
     */
    public function testAuthenticate() {
        $endpointUrl = vsprintf('%s/authenticate', [
            AuthTokenProvider::ENDPOINT_URL,
        ]);
        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'exceptions' => false,
                'json' => [
                    'userIdentifier' => 'SomeUserIdentifier',
                    'userSecret' => 'SomeSecretKey',
                ],
            ])
            ->willReturn($this->responseMock);

        $this->invokeMethod($this->object, 'authenticate');
    }

    /**
     * Test auth with invalid credentials.
     *
     * If there are invalid credentials then BaseApiAbstract::sendRequest()
     * returns response with 401 status code. It means that BaseApiAbstract
     * will try to re-authenticate and send request again. But we don't need
     * to re-authenticate if sendRequest() method was called from
     * AuthTokenProvider object (invalid credentials).
     *
     * AuthTokenProvider::sendRequest() - 401 - Invalid credentials.
     * [Some]Api::sendRequest() - 401 - expired access token.
     *
     * @expectedException Smartling\Exceptions\SmartlingApiException
     * @expectedExceptionMessage AuthProvider expected to be instance of AuthApiInterface, type given:NULL
     */
    public function testAuthenticateWithInvalidCredentials() {
        $response_mock = $this->getMockBuilder('GuzzleHttp\Psr7\Response')
            ->setMethods(
                array_merge(
                    self::$responseInterfaceMethods,
                    self::$messageInterfaceMethods
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $response_mock->expects($this->any())
            ->method('getStatusCode')
            ->willReturn(401);

        $client_mock = $this->getMockBuilder('GuzzleHttp\Client')
            ->setMethods(
                array_merge(
                    self::$clientInterfaceMethods,
                    self::$hasEmitterInterfaceMethods
                )
            )
            ->disableOriginalConstructor()
            ->getMock();

        $client_mock->expects(self::once())
            ->method('request')
            ->willReturn($response_mock);

        $auth_api_mock = $this->getMockBuilder('Smartling\AuthApi\AuthTokenProvider')
            ->setMethods(NULL)
            ->setConstructorArgs([
                $this->userIdentifier,
                $this->secretKey,
                $client_mock,
            ])
            ->getMock();

        $this->invokeMethod($auth_api_mock, 'authenticate');
    }

}
