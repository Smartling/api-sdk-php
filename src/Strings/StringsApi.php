<?php

use Psr\Log\LoggerInterface;
use Smartling\BaseApiAbstract;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\Strings\Params\GetSourceStringsParameters;

class StringsApi extends BaseApiAbstract
{
    const ENDPOINT_URL = 'https://api.smartling.com/strings-api/v2/projects';

    /**
     * @param AuthApiInterface $authProvider
     * @param string $projectId
     * @param LoggerInterface $logger
     *
     * @return StringsApi
     */
    public static function create(AuthApiInterface $authProvider, $projectId, $logger = null)
    {

        $client = self::initializeHttpClient(self::ENDPOINT_URL);

        $instance = new self($projectId, $client, $logger, self::ENDPOINT_URL);
        $instance->setAuth($authProvider);

        return $instance;
    }

    public function getSourceStrings(GetSourceStringsParameters $params) {

    }


} 