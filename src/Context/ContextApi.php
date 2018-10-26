<?php

namespace Smartling\Context;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Context\Params\MatchContextParameters;
use Smartling\Context\Params\MissingResourcesParameters;
use Smartling\Context\Params\UploadContextParameters;
use Smartling\Context\Params\UploadResourceParameters;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\Wait;
use Smartling\Waitable;

/**
 * Class ContextApi
 *
 * @package Smartling\Project
 */
class ContextApi extends BaseApiAbstract implements Waitable
{

    use Wait;

    const ENDPOINT_URL = 'https://api.smartling.com/context-api/v2/projects';

    /**
     * Makes async operation sync.
     *
     * @param array $data
     * @throws SmartlingApiException
     */
    public function wait(array $data) {
        if (!empty($data['matchId'])) {
            $start_time = time();

            do {
                $delta = time() - $start_time;

                if ($delta > $this->getTimeOut()) {
                    throw new SmartlingApiException(vsprintf('Async operation is not completed after %s seconds.', [$delta]));
                }

                sleep(1);

                $result = $this->getMatchStatus($data['matchId']);
            }
            while ($result['status'] != 'COMPLETED');
        }
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

                if ($data['name'] == 'matchParams') {
                    $data['headers'] = [
                        "Content-Type" => "application/json",
                    ];
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
     * Match context async.
     *
     * @param $contextUid
     * @param \Smartling\Context\Params\MatchContextParameters $params
     *
     * @return array
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function matchContext($contextUid, MatchContextParameters $params = null)
    {
        $endpoint = vsprintf('contexts/%s/match/async', $contextUid);
        $requestData = $this->getDefaultRequestData('json', is_null($params) ? [] : $params->exportToArray());

        return $this->sendRequest($endpoint, $requestData, self::HTTP_METHOD_POST);
    }

    /**
     * Match context sync.
     *
     * @param $contextUid
     * @param \Smartling\Context\Params\MatchContextParameters $params
     *
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function matchContextSync($contextUid, MatchContextParameters $params = null)
    {
        $this->wait($this->matchContext($contextUid, $params));
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
     * Upload and match sync.
     *
     * @param \Smartling\Context\Params\UploadContextParameters $params
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function uploadAndMatchContextSync(UploadContextParameters $params)
    {
        $this->wait($this->uploadAndMatchContext($params));
    }

    /**
     * Get context match status.
     *
     * @param $matchId
     * @return array
     * @throws SmartlingApiException
     */
    public function getMatchStatus($matchId) {
        $endpoint = vsprintf('/match/%s', $matchId);
        $requestData = $this->getDefaultRequestData('query', []);

        return $this->sendRequest($endpoint, $requestData, self::HTTP_METHOD_GET);
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

    /**
     * Render context.
     *
     * @param $contextUid
     * @return array
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    public function renderContext($contextUid)
    {
        $endpoint = vsprintf('contexts/%s/render', $contextUid);
        $requestData = $this->getDefaultRequestData('form_params', []);

        return $this->sendRequest($endpoint, $requestData, self::HTTP_METHOD_POST);
    }

}
