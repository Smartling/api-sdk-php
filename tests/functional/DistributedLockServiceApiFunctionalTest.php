<?php

namespace Smartling\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Smartling\AuthApi\AuthTokenProvider;
use Smartling\DistributedLockService\DistributedLockServiceApi;
use Smartling\Exceptions\SmartlingApiException;

class DistributedLockServiceApiFunctionalTest extends TestCase
{
    private $api;

    public function setUp(): void {
        $projectId = \getenv('project_id');
        $userIdentifier = \getenv('user_id');
        $userSecretKey = \getenv('user_key');

        if (
            empty($projectId) ||
            empty($userIdentifier) ||
            empty($userSecretKey)
        ) {
            $this->fail('Missing required parameters');
        }

        $authProvider = AuthTokenProvider::create($userIdentifier, $userSecretKey);
        $this->api = DistributedLockServiceApi::create($authProvider, $projectId);
    }

    public function testLocking() {
        try {
            $key = 'test';
            $lockTtlSeconds = 60;
            $renewTtlSeconds = 30;
            $result = $this->api->acquireLock($key, $lockTtlSeconds);
            $this->assertInstanceOf(\DateTime::class, $result);
            $this->assertLessThanOrEqual($lockTtlSeconds, (new \DateTime())->diff($result)->s);
            sleep(1);
            $result = $this->api->renewLock($key, $renewTtlSeconds);
            $this->assertInstanceOf(\DateTime::class, $result);
            $this->assertLessThanOrEqual($renewTtlSeconds, (new \DateTime())->diff($result)->s);
            $this->api->releaseLock($key);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }
}
