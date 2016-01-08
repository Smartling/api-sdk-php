<?php

namespace Smartling\AuthApi;

use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Smartling\BaseApiAbstract;
use Smartling\Helpers\HttpVerbHelper;

/**
 * Class AuthTokenProvider
 * @package Smartling\AuthApi
 */
class AuthTokenProvider extends BaseApiAbstract implements AuthApiInterface
{

    const ENDPOINT_URL = 'https://api.smartling.com/auth-api/v2/';

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
        $this->data = $this->sendRequest('authenticate', [], HttpVerbHelper::HTTP_VERB_POST);

        return $this->data['accessToken'];
    }

    /**
     * @inheritdoc
     */
    public function getTokenType()
    {
        return isset($this->data['tokenType']) ? $this->data['tokenType'] : '';
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
        $requestData['userIdentifier'] = $this->getUserIdentifier();
        $requestData['userSecret'] = $this->getSecretKey();

        return parent::sendRequest($uri, $requestData, $method, self::STRATEGY_AUTH);
    }
}
