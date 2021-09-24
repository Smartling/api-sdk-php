<?php

namespace Smartling\DistributedLockService;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Exceptions\SmartlingApiException;

class DistributedLockServiceApi extends BaseApiAbstract
{
    const ENDPOINT_URL = 'https://api.smartling.com/distributed-lock-api/v2/projects';

    /**
     * @param AuthApiInterface $authProvider
     * @param string $projectId
     * @param LoggerInterface $logger
     *
     * @return self
     */
    public static function create(AuthApiInterface $authProvider, $projectId, LoggerInterface $logger = null)
    {
        $instance = new self($projectId, self::initializeHttpClient(self::ENDPOINT_URL), $logger, self::ENDPOINT_URL);
        $instance->setAuth($authProvider);

        return $instance;
    }

    /**
     * @param string $key
     * @param float|int $ttlSeconds
     * @param float|int $timeoutSeconds -1 means "do not try to acquire lock again and fail immediately"
     * @param float|int $waitSeconds -1 means "do not try to acquire lock again and fail immediately"
     * @return \DateTime lock release time
     * @throws SmartlingApiException
     */
    public function acquireLock($key, $ttlSeconds, $timeoutSeconds = -1, $waitSeconds = -1)
    {
        $result = $this->sendRequest('locks', $this->getDefaultRequestData('json', [
            'key' => $key,
            'timeout' => $timeoutSeconds * 1000,
            'ttl' => $ttlSeconds * 1000,
            'wait' => $waitSeconds * 1000,
        ]), self::HTTP_METHOD_POST);
        return new \DateTime($result['releaseTime']);
    }

    /**
     * @param string $key
     * @return void
     * @throws SmartlingApiException
     */
    public function releaseLock($key)
    {
        $this->sendRequest("locks/$key", $this->getDefaultRequestData('json', []), self::HTTP_METHOD_DELETE);
    }

    /**
     * @param string $key
     * @param float|int $ttlSeconds
     * @return \DateTime lock release time
     * @throws SmartlingApiException
     */
    public function renewLock($key, $ttlSeconds)
    {
        $result = $this->sendRequest("locks/$key", $this->getDefaultRequestData('json', ['ttl' => $ttlSeconds * 1000]), self::HTTP_METHOD_PUT);
        return new \DateTime($result['releaseTime']);
    }
}
