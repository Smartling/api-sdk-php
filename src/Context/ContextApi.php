<?php

namespace Smartling\Context;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Context\Params\UploadContextParameters;

/**
 * Class ContextApi
 *
 * @package Smartling\Project
 */
class ContextApi extends BaseApiAbstract
{

    const ENDPOINT_URL = 'https://api.smartling.com/context-api/v2/projects';

    /**
     * Instantiates Context API object.
     *
     * @param AuthApiInterface $authProvider
     * @param string $projectId
     * @param LoggerInterface $logger
     *
     * @return ContextApi
     */
    public static function create(AuthApiInterface $authProvider, $projectId, $logger = null)
    {
        $client = self::initializeHttpClient(self::ENDPOINT_URL);

        $instance = new self($projectId, $client, $logger, self::ENDPOINT_URL);
        $instance->setAuth($authProvider);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    protected function processBodyOptions(array $requestData = []) {
        $opts = parent::processBodyOptions($requestData);
        $key = 'content';

        if (!empty($opts['multipart'])) {
            foreach ($opts['multipart'] as &$data) {
                if ($data['name'] == $key) {
                    $data['contents'] = $this->readFile($data['contents']);
                }
            }
        }

        return $opts;
    }

    /**
     * Returns X-SL-Context-Source header.
     *
     * @return string
     */
    private function getXSLContextSourceHeader() {
        return vsprintf('group=connectors;name=%s;version=%s', [
            static::getCurrentClientId(),
            static::getCurrentClientVersion(),
        ]);
    }

    /**
     * Upload a new context.
     *
     * @param \Smartling\Context\Params\UploadContextParameters $params
     * @return array
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function uploadContext(UploadContextParameters $params)
    {
        $requestData = $this->getDefaultRequestData('multipart', $params->exportToArray());
        $requestData['headers']['X-SL-Context-Source'] = $this->getXSLContextSourceHeader();

        return $this->sendRequest('contexts', $requestData, self::HTTP_METHOD_POST);
    }

  /**
   * Match context.
   *
   * @param $contextUid
   * @return array
   * @throws \Smartling\Exceptions\SmartlingApiException
   */
    public function matchContext($contextUid)
    {
        $endpoint = vsprintf('contexts/%s/match/async', $contextUid);
        $requestData = $this->getDefaultRequestData('form_params', []);
        $requestData['headers']['Content-Type'] = 'application/json';
        $requestData['headers']['X-SL-Context-Source'] = $this->getXSLContextSourceHeader();

        return $this->sendRequest($endpoint, $requestData, self::HTTP_METHOD_POST);
    }

}
