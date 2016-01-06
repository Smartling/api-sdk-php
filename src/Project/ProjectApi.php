<?php

namespace Smartling\Project;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Exceptions\SmartlingApiException;

/**
 * Class ProjectApi
 *
 * @package Smartling\Project
 */
class ProjectApi extends BaseApiAbstract
{
    const ENDPOINT_URL = 'https://api.smartling.com/projects-api/v2/projects';

    /**
     * @param AuthApiInterface $authProvider
     * @param string $projectId
     * @param LoggerInterface $logger
     *
     * @return ProjectApi
     */
    public static function create(AuthApiInterface $authProvider, $projectId, $logger = null)
    {

        $client = self::initializeHttpClient(self::ENDPOINT_URL);

        $instance = new self($projectId, $client, $logger, self::ENDPOINT_URL);
        $instance->setAuth($authProvider);

        return $instance;
    }

    /**
     * @return array
     * @throws SmartlingApiException
     */
    public function getProjectDetails()
    {
        return $this->sendRequest('', [], self::HTTP_METHOD_GET);
    }
}