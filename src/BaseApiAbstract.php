<?php

namespace Smartling;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\Logger\DevNullLogger;

/**
 * Class BaseApiAbstract
 *
 * @package Smartling\Api
 */
abstract class BaseApiAbstract
{
    const HTTP_METHOD_GET = 'GET';
    const HTTP_METHOD_POST = 'POST';
    const HTTP_METHOD_DELETE = 'DELETE';


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
     * @param bool $httpErrors
     *
     * @return array
     */
    protected function prepareHeaders($httpErrors = false)
    {
        $options = [
            'headers' => [
                'Accept' => 'application/json',
            ],
            'http_errors' => $httpErrors,
        ];


        $accessToken = $this->getAuth()->getAccessToken();
        $tokenType = $this->getAuth()->getTokenType();
        $options['headers']['Authorization'] =
           vsprintf(' %s %s', [$tokenType, $accessToken]);

        return $options;
    }

    /**
     * @param string $uri
     *
     * @return string
     */
    private function normalizeUri($uri = '')
    {
        $endpoint = rtrim($this->getBaseUrl(), '/') . '/' . ltrim($uri, '/');

        return $endpoint;
    }

    /**
     * @param int $responseStatusCode
     * @throws SmartlingApiException
     */
    private function checkAuthenticationError($responseStatusCode)
    {
        //Special handling for 401 error - authentication error => expire token
        if (401 === (int)$responseStatusCode) {
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
     * @param string $responseStatusCode
     * @param string $responseBody
     *
     * @throws SmartlingApiException
     */
    private function processErrors($responseStatusCode, $responseBody)
    {
        // Catch all errors from Smartling and throw appropriate exception.
        if ($responseStatusCode >= 400) {
            $errorResponse = json_decode($responseBody, true);

            if (!$errorResponse
                || !is_array($errorResponse)
                || !array_key_exists('response', $errorResponse)
                || !is_array($errorResponse['response'])
                || !array_key_exists('errors', $errorResponse['response'])
                || empty($errorResponse['response']['errors'])
            ) {
                $message = 'Bad response format from Smartling';
                $this->getLogger()->error($message);
                throw new SmartlingApiException($message);
            }

            $error_msg = array_map(
                function ($a) {
                    return $a['message'];
                },
                $errorResponse['response']['errors']
            );

            $message = implode(' || ', $error_msg);

            $this->getLogger()->error($message);
            throw new SmartlingApiException($message, $responseStatusCode);
        }
    }

    /**
     * @param array $options
     * @param array $requestData
     * @param string $method
     *
     * @return array
     */
    protected function mergeRequestData($options, $requestData, $method = self::HTTP_METHOD_GET)
    {
        if (in_array($method, [self::HTTP_METHOD_GET, self::HTTP_METHOD_DELETE])) {
            $options['query'] = $requestData;
            return $options;
        }

        if (!isset($options['multipart']))
        {
            $options['multipart'] = [];
        }

        foreach ($requestData as $key => $value) {
            // Hack to cast FALSE to '0' instead of empty string.
            if (is_bool($value)) {
                $value = (int)$value;
            }

            if (is_array($value)) {
                foreach ($value as $_item) {
                    $options['multipart'][] = [
                      'name' => $key . '[]',
                      'contents' => (string)$_item,
                    ];
                }
            } else {
               $options['multipart'][] = [
                  'name' => $key,
                  'contents' => (string)$value,
               ];
            }
        }

        return $options;
    }

    protected function processResponse($responseBody) {
        $response = json_decode($responseBody, true);

        // Throw exception if json is not valid.
        if (!$response
          || !is_array($response)
          || !array_key_exists('response', $response)
          || !is_array($response['response'])
          || empty($response['response']['code'])
          || $response['response']['code'] !== 'SUCCESS'
        ) {
            $message = 'Bad response format from Smartling';
            $this->getLogger()->error($message);
            throw new SmartlingApiException($message);
        }

        return isset($response['response']['data']) ? $response['response']['data'] : true;
    }
    /**
     * @param string $uri
     * @param array $requestData
     * @param string $method
     *
     * @return  bool true on SUCCESS and empty data
     *          string on $doProcessResponseBody = false
     *          array otherwise
     * @throws SmartlingApiException
     */
    protected function sendRequest($uri, array $requestData, $method)
    {

        $options = $this->prepareHeaders();
        $options = $this->mergeRequestData($options, $requestData, $method);

        $endpoint = $this->normalizeUri($uri);

        $guzzleResponse = $this->getHttpClient()->request($method, $endpoint, $options);

        $this->getLogger()->debug(
            json_encode(
                [
                    'request' => [
                        'endpoint' => $endpoint,
                        'method' => $method,
                        'requestData' => $options,
                    ],
                    'response' => [
                        'statusCode' => $guzzleResponse->getStatusCode(),
                        'headers' => $guzzleResponse->getHeaders(),
                        'body' => (string)$guzzleResponse->getBody(),
                    ],
                ],
                JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT
            )
        );

        $responseBody = (string)$guzzleResponse->getBody();
        $responseStatusCode = $guzzleResponse->getStatusCode();

        if (400 <= $responseStatusCode) {
            $this->checkAuthenticationError($responseStatusCode);
            $this->processErrors($responseStatusCode, $responseBody);
        }

        return $this->processResponse($responseBody);
    }
}