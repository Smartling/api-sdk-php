<?php

namespace Smartling\Tests\Unit;

use Smartling\BaseApiAbstract;
use Smartling\File\FileApi;

class BaseApiAbstractTest extends ApiTestAbstract
{
    public function setUp(): void
    {
        parent::setUp();

        // Restore user agent specific static values.
        BaseApiAbstract::setCurrentClientId(BaseApiAbstract::CLIENT_LIB_ID_SDK);
        BaseApiAbstract::setCurrentClientVersion(BaseApiAbstract::CLIENT_LIB_ID_VERSION);
        BaseApiAbstract::setCurrentClientUserAgentExtension(BaseApiAbstract::CLIENT_USER_AGENT_EXTENSION);
    }

    /**
     * Test default user agent.
     */
    public function testNoUserAgentExtensions()
    {
        $instance = FileApi::create($this->authProvider, 'test');
        $http_client = $this->invokeMethod($instance, 'getHttpClient');

        $this->assertEquals('smartling-api-sdk-php/4.0.5 (no extensions) GuzzleHttp/7', $http_client->getConfig()['headers']['User-Agent']);
    }

    /**
     * Test custom client id and version in user agent.
     */
    public function testCurrentClientIdAndVersionSpecifiedUserAgentExtensionNotSpecified()
    {
        BaseApiAbstract::setCurrentClientId('php-connector');
        BaseApiAbstract::setCurrentClientVersion('1.2.3');

        $instance = FileApi::create($this->authProvider, 'test');
        $http_client = $this->invokeMethod($instance, 'getHttpClient');

        $this->assertEquals('php-connector/1.2.3 (no extensions) GuzzleHttp/7', $http_client->getConfig()['headers']['User-Agent']);
    }

    /**
     * Test custom client id, version and extension in user agent.
     */
    public function testClientIdAndClientVersionAndUserAgentExtensionsSpecified()
    {
        BaseApiAbstract::setCurrentClientId('php-connector');
        BaseApiAbstract::setCurrentClientVersion('1.2.3');
        BaseApiAbstract::setCurrentClientUserAgentExtension('dependency-1/version-1 dependency-2/version-2');

        $instance = FileApi::create($this->authProvider, 'test');
        $http_client = $this->invokeMethod($instance, 'getHttpClient');

        $this->assertEquals('php-connector/1.2.3 dependency-1/version-1 dependency-2/version-2 GuzzleHttp/7', $http_client->getConfig()['headers']['User-Agent']);
    }
}
