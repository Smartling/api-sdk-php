<?php

namespace Smartling\Context;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Context\Params\MissingResourcesParameters;
use Smartling\Context\Params\UploadContextParameters;
use Smartling\Context\Params\UploadResourceParameters;
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
     * @throws \InvalidArgumentException
     */
    public function setTimeOut($timeOut) {
        if ($timeOut <= 0) {
            throw new \InvalidArgumentException('Timeout value must be more or grater then zero.');
        }

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
        $keys = ['content', 'resource'];

        if (!empty($opts['multipart'])) {
            foreach ($opts['multipart'] as &$data) {
                if (in_array($data['name'], $keys)) {
                    $data['contents'] = $this->readFile($data['contents']);
                }
            }
        }

        return $opts;
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefaultRequestData($parametersType, $parameters, $auth = true, $httpErrors = false) {
        $requestData = parent::getDefaultRequestData($parametersType, $parameters, $auth = true, $httpErrors = false);
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
     * @param \Smartling\Context\Params\UploadContextParameters $params
     * @return array
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function uploadContext(UploadContextParameters $params)
    {
        $requestData = $this->getDefaultRequestData('multipart', $params->exportToArray());

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

        return $this->sendRequest($endpoint, $requestData, self::HTTP_METHOD_POST);
    }

    /**
     * Upload and match async.
     *
     * @param \Smartling\Context\Params\UploadContextParameters $params
     * @return array
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function uploadAndMatchContext(UploadContextParameters $params)
    {
        $requestData = $this->getDefaultRequestData('multipart', $params->exportToArray());

        return $this->sendRequest('contexts/upload-and-match-async', $requestData, self::HTTP_METHOD_POST);
    }

    /**
     * Get missing resources.
     *
     * @param MissingResourcesParameters|null $params
     * @return array
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function getMissingResources(MissingResourcesParameters $params = null) {
        $requestData = $this->getDefaultRequestData('query', is_null($params) ? [] : $params->exportToArray());

        return $this->sendRequest('missing-resources', $requestData, self::HTTP_METHOD_GET);
    }

    /**
     * Get all missing resources.
     *
     * @return array
     * Contains next keys:
     *  - "items": array of missing resources
     *  - "all": boolean which indicates whether function has read all the
     *           available items or not.
     *
     * @throws SmartlingApiException
     */
    public function getAllMissingResources() {
        $missingResources = [];
        $offset = FALSE;
        $all = TRUE;
        $start_time = time();

        while (!is_null($offset)) {
            $delta = time() - $start_time;

            if ($delta > $this->getTimeOut()) {
                $all = FALSE;
                break;
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

        return [
            'items' => $missingResources,
            'all' => $all,
        ];
    }

    /**
     * Upload resource.
     *
     * @param $resourceId
     * @param \Smartling\Context\Params\UploadResourceParameters $params
     * @return bool
     * @throws SmartlingApiException
     */
    public function uploadResource($resourceId, UploadResourceParameters $params)
    {
        $endpoint = vsprintf('resources/%s', $resourceId);
        $requestData = $this->getDefaultRequestData('multipart', $params->exportToArray());

        return $this->sendRequest($endpoint, $requestData, self::HTTP_METHOD_PUT);
    }

}
