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
     * @return \DateTime lock release time
     * @throws SmartlingApiException
     */
    public function acquireLock($key, $ttlSeconds)
    {
        $result = $this->sendRequest('locks', $this->getDefaultRequestData('json', ['key' => $key, 'ttl' => $ttlSeconds * 1000]), self::HTTP_METHOD_POST);
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
