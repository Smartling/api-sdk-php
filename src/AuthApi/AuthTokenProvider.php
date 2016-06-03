<?php

namespace Smartling\AuthApi;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Smartling\BaseApiAbstract;

/**
 * Class AuthTokenProvider
 * @package Smartling\AuthApi
 */
class AuthTokenProvider extends BaseApiAbstract implements AuthApiInterface
{

    const ENDPOINT_URL = 'https://api.smartling.com/auth-api/v2/';

    const RESPONSE_KEY_ACCESS_TOKEN = 'accessToken';
    const RESPONSE_KEY_ACCESS_TOKEN_TTL = 'expiresIn';
    const RESPONSE_KEY_REFRESH_TOKEN = 'refreshToken';
    const RESPONSE_KEY_REFRESH_TOKEN_TTL = 'refreshExpiresIn';
    const RESPONSE_KEY_TOKEN_TYPE = 'tokenType';

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
    private $data = [];

    /**
     * @var int
     */
    private $requestTimestamp = 0;

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
     * Checks if token exists
     */
    private function tokenExists()
    {
        return is_array($this->data) && array_key_exists(self::RESPONSE_KEY_ACCESS_TOKEN, $this->data);
    }

    /**
     * Checks if token is expired
     */
    private function tokenExpired()
    {
        return $this->tokenExists() &&
        time() > $this->requestTimestamp + $this->data[self::RESPONSE_KEY_ACCESS_TOKEN_TTL];
    }

    /**
     * Sends /authenticate request
     */
    private function authenticate()
    {
        $requestData = [
            'userIdentifier' => $this->getUserIdentifier(),
            'userSecret' => $this->getSecretKey()
        ];

        $this->requestTimestamp = time();

        return $this->sendRequest('authenticate', $requestData, self::HTTP_METHOD_POST);
    }

    /**
     * Renews tokens
     */
    private function tokenRenew()
    {
        if ($this->tokenExists() && $this->tokenCanBeRenewed()) {
            $requestData = [
                'refreshToken' => $this->data[self::RESPONSE_KEY_REFRESH_TOKEN]
            ];
            return $this->sendRequest('authenticate/refresh', $requestData, self::HTTP_METHOD_POST);
        } else {
            return $this->authenticate();
        }
    }

    /**
     * Checks if token can be renewed
     */
    private function tokenCanBeRenewed()
    {
        return $this->tokenExists() &&
        time() > $this->requestTimestamp + $this->data[self::RESPONSE_KEY_REFRESH_TOKEN_TTL];
    }

    /**
     * @inheritdoc
     */
    public function getAccessToken()
    {
        if (!$this->tokenExists()) {
            $this->requestTimestamp = time();
            $this->data = $this->authenticate();
        }
        if ($this->tokenExpired()) {
            $this->tokenRenew();
        }
        return $this->data[self::RESPONSE_KEY_ACCESS_TOKEN];
    }

    /**
     * @inheritdoc
     */
    public function getTokenType()
    {
        return $this->tokenExists() ? $this->data[self::RESPONSE_KEY_TOKEN_TYPE] : '';
    }

    /**
     * @inheritdoc
     */
    public function resetToken()
    {
        $this->data = [];
    }

    protected function sendRequest($uri, array $requestData, $method, $strategy = self::STRATEGY_GENERAL)
    {
        return parent::sendRequest($uri, $requestData, $method, self::STRATEGY_AUTH);
    }
}
