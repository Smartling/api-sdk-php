<?php

namespace Smartling\Context;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Context\Params\MissingResourcesParameters;
use Smartling\Context\Params\UploadContextParameters;
use Smartling\Exceptions\SmartlingApiException;

/**
 * Class ContextApi
 *
 * @package Smartling\Project
 */
class ContextApi extends BaseApiAbstract
{

    const ENDPOINT_URL = 'https://api.smartling.com/context-api/v2/projects';

    /**
     * Timeout in seconds.
     *
     * @var int
     */
    private $timeOut = 15;

    /**
     * @return int
     */
    public function getTimeOut() {
        return $this->timeOut;
    }
    /**
     * @param int $timeOut
     */
    public function setTimeOut($timeOut) {
        $this->timeOut = $timeOut;
    }

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
    protected function processBodyOptions($requestData = []) {
        $opts = parent::processBodyOptions($requestData);
        $key = 'content';

        if (array_key_exists($key, $opts)) {
            $opts[$key] = $this->readFile($opts[$key]);
        }

        return $opts;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultRequestData($parametersType, $parameters, $auth = true, $httpErrors = false) {
        $requestData = parent::getDefaultRequestData($parametersType, $parameters, $auth, $httpErrors);
        $requestData['headers']['X-SL-Context-Source'] = $this->getXSLContextSourceHeader();

        return $requestData;
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
     * @param UploadContextParameters $params
     * @return bool
     * @throws SmartlingApiException
     */
    public function uploadContext(UploadContextParameters $params)
    {
        $requestData = $this->getDefaultRequestData('body', $params->exportToArray());
        $requestData['headers']['Content-Type'] = 'application/json';
        $request = $this->prepareHttpRequest('contexts', $requestData, self::HTTP_METHOD_POST);

        return $this->sendRequest($request);
    }

  /**
   * Match context.
   *
   * @param $contextUid
   * @return bool
   * @throws SmartlingApiException
   */
    public function matchContext($contextUid)
    {
        $endpoint = vsprintf('contexts/%s/match/async', $contextUid);
        $requestData = $this->getDefaultRequestData('body', '');
        $requestData['headers']['Content-Type'] = 'application/json';
        $request = $this->prepareHttpRequest($endpoint, $requestData, self::HTTP_METHOD_POST);

        return $this->sendRequest($request);
    }

    /**
     * Get missing resources.
     *
     * @param MissingResourcesParameters $params
     * @return array
     * @throws SmartlingApiException
     */
    public function getMissingResources(MissingResourcesParameters $params = NULL)
    {
        $requestData = $this->getDefaultRequestData('query', is_null($params) ? [] : $params->exportToArray());
        $requestData['headers']['Content-Type'] = 'application/json';
        $request = $this->prepareHttpRequest('missing-resources', $requestData, self::HTTP_METHOD_GET);

        return $this->sendRequest($request);
    }

    /**
     * Get all missing resources.
     *
     * @return array
     * @throws SmartlingApiException
     */
    public function getAllMissingResources()
    {
        $missingResources = [];
        $offset = FALSE;
        $start_time = time();

        while (!is_null($offset)) {
            $delta = time() - $start_time;

            if ($delta > $this->getTimeOut()) {
                throw new SmartlingApiException(vsprintf('Not all missing resources received after %s seconds.', [$delta]));
            }

            if (!$offset) {
                $params = NULL;
            }
            else {
                $params = new MissingResourcesParameters();
                $params->setOffset($offset);
            }

            $response = $this->getMissingResources($params);
            $offset = !empty($response['offset']) ? $response['offset'] : NULL;
            $missingResources = array_merge($missingResources, $response['items']);
        }

        return $missingResources;
    }

}
