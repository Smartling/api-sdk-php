<?php

namespace Smartling\AuthApi;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Smartling\BaseApiAbstract;

class AuthTokenProvider extends BaseApiAbstract implements AuthApiInterface
{

    const ENDPOINT_URL = 'https://api.smartling.com/auth-api/v2/';

    const TIME_TO_RESFRESH = 2;

    /**
     * @var string
     */
    private $userIdentifier;

    /**
     * @var string
     */
    private $secretKey;

    /**
     * @var array
     */
    private $data;

    /**
     * @var int
     */
    private $requestTime = 0;

    /**
     * @return string
     */
    private function getUserIdentifier()
    {
        return $this->userIdentifier;
    }

    /**
     * @param string $userIdentifier
     */
    private function setUserIdentifier($userIdentifier)
    {
        $this->userIdentifier = $userIdentifier;
    }

    /**
     * @return string
     */
    private function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * @param string $secretKey
     */
    private function setSecretKey($secretKey)
    {
        $this->secretKey = $secretKey;
    }

    /**
     * AuthTokenProvider constructor.
     *
     * @param string $userIdentifier
     * @param string $secretKey
     * @param ClientInterface $client
     * @param LoggerInterface $logger
     */
    public function __construct($userIdentifier, $secretKey, ClientInterface $client, $logger = null)
    {
        parent::__construct('', $client, $logger, self::ENDPOINT_URL);

        $this->setUserIdentifier($userIdentifier);
        $this->setSecretKey($secretKey);
    }

    /**
     * Creates and returns instance of AuthTokenProvider
     *
     * @param string $userIdentifier
     * @param string $secretKey
     * @param LoggerInterface $logger
     *
     * @return AuthTokenProvider
     */
    public static function create($userIdentifier, $secretKey, $logger = null)
    {
        $client = self::initializeHttpClient(self::ENDPOINT_URL);

        return new self($userIdentifier, $secretKey, $client, $logger);
    }


    /**
     * @inheritdoc
     */
    public function getAccessToken()
    {
        if ($this->isValidToken())
        {
            return $this->data['accessToken'];
        }

        if ($this->isValidRefreshToken())
        {
            $this->data = $this->refreshToken();
        }
        else
        {
            $this->data = $this->authenticate();
        }

        return $this->data['accessToken'];
    }

    /**
     * @inheritdoc
     */
    public function getTokenType()
    {
        return isset($this->data['tokenType']) ? $this->data['tokenType'] : '';
    }

    public function getRefreshToken()
    {
        return isset($this->data['refreshToken']) ? $this->data['refreshToken'] : '';
    }

    private function getTokenExpirationTime() {
        return isset($this->data['expiresIn']) ? $this->data['expiresIn'] : 0;
    }

    private function isValidToken()
    {
        return time() + self::TIME_TO_RESFRESH < $this->requestTime + $this->getTokenExpirationTime();
    }

    private function getRefreshTokenExpirationTime() {
        return isset($this->data['refreshExpiresIn']) ? $this->data['refreshExpiresIn'] : 0;
    }

    private function isValidRefreshToken()
    {
        return time() + self::TIME_TO_RESFRESH < $this->requestTime + $this->getRefreshTokenExpirationTime();
    }

    /**
     * @inheritdoc
     */
    public function resetToken()
    {
        $this->data = [];
    }

    /**
     * @inheritdoc
     */
    protected function prepareHeaders($doProcessResponseBody, $httpErrors = false) {
        $options = [
          'headers' => [
            'Accept' => 'application/json',
          ],
          'http_errors' => $httpErrors,
        ];

        return $options;
    }

    /**
     * @inheritdoc
     */
    protected function mergeRequestData($options, $requestData, $method = self::HTTP_METHOD_GET)
    {
        $options['json'] = $requestData;
        return $options;
    }


    protected function authenticate()
    {
        $requestData = [];
        $requestData['userIdentifier'] = $this->getUserIdentifier();
        $requestData['userSecret'] = $this->getSecretKey();

        $this->requestTime = time();

        return $this->sendRequest('authenticate', $requestData, self::HTTP_METHOD_POST);
    }

    protected function refreshToken()
    {
        $requestData['refreshToken'] = $this->getRefreshToken();

        $this->requestTime = time();

        return $this->sendRequest('authenticate/refresh', $requestData, self::HTTP_METHOD_POST);
    }
}
