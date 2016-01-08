<?php

namespace Smartling;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\Helpers\HttpVerbHelper;
use Smartling\Logger\DevNullLogger;

/**
 * Class BaseApiAbstract
 *
 * @package Smartling\Api
 */
abstract class BaseApiAbstract
{

    const STRATEGY_GENERAL = 'general';

    const STRATEGY_DOWNLOAD = 'download';

    const STRATEGY_AUTH = 'auth';

    /**
     * Project Id in Smartling dashboard
     *
     * @var string
     */
    private $projectId;

    /**
     * Smartling API base url.
     *
     * @var string
     */
    private $baseUrl;

    /**
     * @var AuthApiInterface
     */
    private $auth;

    /**
     * Http Client abstraction.
     *
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * Logger.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @return string
     */
    protected function getProjectId()
    {
        return $this->projectId;
    }

    /**
     * @param string $projectId
     */
    protected function setProjectId($projectId)
    {
        $this->projectId = $projectId;
    }

    /**
     * @return string
     */
    protected function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param string $baseUrl
     */
    protected function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    /**
     * @return AuthApiInterface
     */
    protected function getAuth()
    {
        return $this->auth;
    }

    /**
     * @param AuthApiInterface $auth
     */
    protected function setAuth(AuthApiInterface $auth)
    {
        $this->auth = $auth;
    }

    /**
     * @return ClientInterface
     */
    protected function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param ClientInterface $httpClient
     */
    protected function setHttpClient($httpClient)
    {
        $this->httpClient = $httpClient;
    }

    /**
     * @return LoggerInterface
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param LoggerInterface $logger
     */
    protected function setLogger($logger)
    {
        $this->logger = $logger;
    }


    /**
     * BaseApiAbstract constructor.
     *
     * @param string $projectId
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     * @param string|null $service_url
     */
    public function __construct($projectId, ClientInterface $client, $logger = null, $service_url = null)
    {
        $this->setProjectId($projectId);
        $this->setHttpClient($client);

        if (is_null($logger)) {
            $logger = new DevNullLogger();
        }

        $this->setLogger($logger);

        $this->setBaseUrl(rtrim($service_url, '/') . '/' . $projectId);
    }

    /**
     * @param string $serviceUrl
     * @param bool $debug
     *
     * @return Client
     */
    protected static function initializeHttpClient($serviceUrl, $debug = false)
    {
        $client = new Client(
            [
                'base_uri' => $serviceUrl,
                'debug' => $debug,
            ]
        );

        return $client;
    }

    /**
     * OOP wrapper for fopen() function.
     *
     * @param string $realPath
     *   Real path for file.
     *
     * @return resource
     *
     * @throws \Smartling\Exceptions\SmartlingApiException
     */
    protected function readFile($realPath)
    {
        $stream = @fopen($realPath, 'r');

        if (!$stream) {
            throw new SmartlingApiException("File $realPath was not able to be read.");
        } else {
            return $stream;
        }
    }

    /**
     * @param string $strategy
     * @param bool $httpErrors
     *
     * @return array
     */
    private function prepareOptions($strategy, $httpErrors = false)
    {
        $options = [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'exceptions' => $httpErrors,
        ];

        if (self::STRATEGY_AUTH !== $strategy) {
            $accessToken = $this->getAuth()->getAccessToken();
            $tokenType = $this->getAuth()->getTokenType();
            $options['headers']['Authorization'] =
                vsprintf(' %s %s', [$tokenType, $accessToken]);
        }

        if (self::STRATEGY_DOWNLOAD === $strategy) {
            unset($options['headers']['Accept']);
        }

        return $options;
    }

    /**
     * @param array $options
     * @param array $requestData
     *
     * @return array
     */
    private function addRequestDataToOptions(array $options, array $requestData = [])
    {
        $opts = [];
        foreach ($requestData as $key => $value) {
            // Hack to cast FALSE to '0' instead of empty string.
            if (is_bool($value)) {
                $value = (int)$value;
            }
            if ('file' === $key) {
                $value = $this->readFile($value);
            }
            $opts[$key] = $value;
        }
        return $opts;
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    private function normalizeUri($uri = '')
    {
        return rtrim($this->getBaseUrl(), '/') . '/' . ltrim($uri, '/');
    }

    /**
     * @param ResponseInterface $response
     * @throws SmartlingApiException
     */
    private function checkAuthenticationError(ResponseInterface $response)
    {
        //Special handling for 401 error - authentication error => expire token
        if (401 === (int)$response->getStatusCode()) {
            if (!($this->getAuth() instanceof AuthApiInterface)) {
                $type = gettype($this->getAuth());
                if ('object' === $type) {
                    $type .= '::' . get_class($this->getAuth());
                }
                throw new SmartlingApiException('AuthProvider expected to be instance of AuthApiInterface, type given:' . $type);
            } else {
                $this->getAuth()->resetToken();
            }
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws SmartlingApiException
     */
    private function processErrors(ResponseInterface $response)
    {
        // Catch all errors from Smartling and throw appropriate exception.
        if (400 <= (int)$response->getStatusCode()) {
            $this->processError($response);
        }
    }

    /**
     * @param ResponseInterface $response
     * @throws SmartlingApiException
     */
    private function processError($response)
    {
        try {
            $json = $response->json();

            if (!array_key_exists('response', $json)
                || !is_array($json['response'])
                || !array_key_exists('errors', $json['response'])
                || empty($json['response']['errors'])
            ) {
                $message = 'Bad response format from Smartling';
                $this->getLogger()->error($message);
                throw new SmartlingApiException($message);
            }

            $error_msg = array_map(
                function ($a) {
                    return $a['message'];
                },
                $json['response']['errors']
            );

            $message = implode(' || ', $error_msg);

            $this->getLogger()->error($message);
            throw new SmartlingApiException($message, $response->getStatusCode());

        } catch (RuntimeException $e) {
            $message = vsprintf('Bad response format from Smartling: %s', [$response->getBody()]);
            $this->getLogger()->error($message);
            throw new SmartlingApiException($message);
        }
    }

    /**
     * @param string $uri
     * @param array $requestData
     * @param string $method
     * @param string $strategy
     * @return RequestInterface
     */
    private function prepareHttpRequest($uri, array $requestData, $method, $strategy)
    {
        $options = $this->prepareOptions($strategy);

        if (in_array($method, [HttpVerbHelper::HTTP_VERB_GET, HttpVerbHelper::HTTP_VERB_DELETE], true)) {
            $options['query'] = $requestData;
        } else {
            if (self::STRATEGY_AUTH === $strategy) {
                $options['json'] = $requestData;
            } else {
                $options['body'] = $this->addRequestDataToOptions($options, $requestData);
            }
        }

        $endpoint = $this->normalizeUri($uri);

        $options['exceptions'] = false;

        $clientRequest = $this->getHttpClient()->createRequest($method, $endpoint, $options);

        $this->getLogger()->debug(
            json_encode(
                [
                    'request' => [
                        'endpoint' => $endpoint,
                        'method' => $method,
                        'requestData' => $options,
                    ],
                ],
                JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT
            )
        );

        return $clientRequest;
    }

    /**
     * @param string $uri
     * @param array $requestData
     * @param string $method
     * @param string $strategy
     *
     * @return  bool true on SUCCESS and empty data
     *          string on $processResponseBody = false
     *          array otherwise
     * @throws SmartlingApiException
     */
    protected function sendRequest($uri, array $requestData, $method, $strategy = self::STRATEGY_GENERAL)
    {
        $request = $this->prepareHttpRequest($uri, $requestData, $method, $strategy);

        try {
            $response = $this->getHttpClient()->send($request);
        } catch (RequestException $e) {
            $message = vsprintf('Guzzle:RequestException: %s', [$e->getMessage(),]);
            $this->getLogger()->error($message);
            throw new SmartlingApiException($message, 0, $e);
        } catch (\LogicException $e) {
            $message = vsprintf('Guzzle:LogicException: %s', [$e->getMessage()]);
            $this->getLogger()->error($message);
            throw new SmartlingApiException($message, 0, $e);
        } catch (\Exception $e) {
            $message = vsprintf('Guzzle:Exception: %s', [$e->getMessage()]);
            $this->getLogger()->error($message);
            throw new SmartlingApiException($message, 0, $e);
        }

        $this->getLogger()->debug(
            json_encode(
                [
                    'response' => [
                        'statusCode' => $response->getStatusCode(),
                        'headers' => $response->getHeaders(),
                        'body' => (string)$response->getBody(),
                    ],
                ],
                JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT
            )
        );

        if (400 <= (int)$response->getStatusCode()) {
            $this->checkAuthenticationError($response);
            $this->processErrors($response);
        }

        switch ($strategy) {
            case self::STRATEGY_DOWNLOAD: {
                return $response->getBody();
                break;
            }
            case self::STRATEGY_AUTH:
            case self::STRATEGY_GENERAL:
            default: {

                try {
                    $json = $response->json();

                    if (!array_key_exists('response', $json)
                        || !is_array($json['response'])
                        || empty($json['response']['code'])
                        || $json['response']['code'] !== 'SUCCESS'
                    ) {
                        $this->processError($response);
                    }

                    return isset($json['response']['data']) ? $json['response']['data'] : true;

                } catch (RuntimeException $e) {
                    $this->processError($response);
                }
            }
        }
    }
}