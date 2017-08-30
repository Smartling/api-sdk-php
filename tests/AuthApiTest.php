<?php

namespace Smartling\Tests;

use Smartling\AuthApi\AuthTokenProvider;

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
            ->method('createRequest')
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
            ->willReturn($this->requestMock);

        $this->client->expects(self::once())
            ->method('send')
            ->with($this->requestMock)
            ->willReturn($this->responseMock);

        $this->invokeMethod($this->object, 'authenticate');
    }

}
