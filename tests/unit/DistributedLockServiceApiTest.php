<?php

namespace Smartling\Tests\Unit;
use GuzzleHttp\Psr7\Response;
use Smartling\BaseApiAbstract;
use Smartling\DistributedLockService\DistributedLockServiceApi;

class DistributedLockServiceApiTest extends ApiTestAbstract
{
    public function testAcquireLock() {
        $endpointUrl = 'locks';

        $responseMock = $this->getMock(Response::class);
        $responseMock->method('getBody')
            ->willReturn('{"response":{"data":{"key":"test","releaseTime":"2019-10-01T12:22:00Z"},"code":"SUCCESS"}}');

        $this->setExpectations(
            BaseApiAbstract::HTTP_METHOD_POST,
            DistributedLockServiceApi::ENDPOINT_URL . '/' . $this->projectId . '/' . $endpointUrl,
            [
                'key' => 'test',
                'ttl' => 5000,
                'timeout' => -1000,
                'wait' => -1000,
            ],
            $responseMock
        );
        $this->getApiMock()->acquireLock('test', 5);
    }

    public function testReleaseLock() {
        $endpointUrl = 'locks/test';

        $responseMock = $this->getMock(Response::class);
        $responseMock->method('getBody')->willReturn('{"response":{"data":null,"code":"SUCCESS"}}');

        $this->setExpectations(
            BaseApiAbstract::HTTP_METHOD_DELETE,
            DistributedLockServiceApi::ENDPOINT_URL . '/' . $this->projectId . '/' . $endpointUrl,
            [],
            $responseMock
        );
        $this->getApiMock()->releaseLock('test');
    }

    public function testRenewLock() {
        $endpointUrl = 'locks/test';

        $responseMock = $this->getMock(Response::class);
        $responseMock->method('getBody')
            ->willReturn('{"response":{"data":{"key":"test","releaseTime":"2019-10-01T12:22:00Z"},"code":"SUCCESS"}}');

        $this->setExpectations(
            BaseApiAbstract::HTTP_METHOD_PUT,
            DistributedLockServiceApi::ENDPOINT_URL . '/' . $this->projectId . '/' . $endpointUrl,
            [
                'ttl' => 5000,
            ],
            $responseMock
        );
        $this->getApiMock()->renewLock('test', 5);
    }

    private function setExpectations($method, $endpoint, $json, $response) {
        $this->client
            ->expects(self::once())
            ->method('request')
            ->with($method, $endpoint, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "{$this->authProvider->getTokenType()} {$this->authProvider->getAccessToken()}",
                ],
                'exceptions' => false,
                'json' => $json,
            ])
            ->willReturn($response);
    }

    private function getApiMock()
    {
        $x = new DistributedLockServiceApi($this->projectId, $this->client, null, DistributedLockServiceApi::ENDPOINT_URL);
        $this->invokeMethod($x, 'setAuth', [$this->authProvider]);
        return $x;
    }
}
