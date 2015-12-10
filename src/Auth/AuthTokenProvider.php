<?php

namespace Smartling\Auth;

use GuzzleHttp\ClientInterface;
use Smartling\Logger\DevNullLogger;
use Smartling\Logger\LoggerInterface;
use GuzzleHttp\Client;

class AuthTokenProvider implements AuthApiInterface {
  protected $userIdentifier;
  protected $secretKey;
  protected $logger;
  protected $httpClient;
  protected static $apiUrl = 'https://api.smartling.com/auth-api/v2/';

  public function __construct($userIdentifier, $secretKey, ClientInterface $http_client, LoggerInterface $logger) {

    $this->userIdentifier = $userIdentifier;
    $this->secretKey = $secretKey;

    $this->logger = $logger;
    $this->httpClient = $http_client;
  }

  public static function create($userIdentifier, $secretKey) {
    $client = new Client(['base_uri' => self::$apiUrl, 'debug' => TRUE]);
    $logger = new DevNullLogger();

    return new static($userIdentifier, $secretKey, $client, $logger);
  }

  public function setHttpClient(ClientInterface $http_client) {
    $this->httpClient = $http_client;
  }

  public function setLogger(LoggerInterface $logger) {
    $this->logger = $logger;
  }


  public function getAccessToken() {
    return $this->sendRequest('authenticate', [], 'POST');
  }


  protected function sendRequest($uri, $requestData, $method) {
    // Set api key and product id as required arguments.
    $requestData['apiKey'] = $this->apiKey;
    $requestData['projectId'] = $this->projectId;

    // Ask for JSON and disable Guzzle exceptions.
    $options = [
      'headers' => [
        'Accept' => 'application/json',
      ],
      'http_errors' => FALSE,
    ];

    // For GET and DELETE http methods use just query parameter builder.
    if (in_array($method, ['GET', 'DELETE'])) {
      $options['query'] = $requestData;
    } else {
      $options['multipart'] = [];

      foreach ($requestData as $key => $value) {
        // Hack to cast FALSE to '0' instead of empty string.
        if (is_bool($value)) {
          $value = (int)$value;
        }
        $options['multipart'][] = [
          'name' => $key,
          // Typecast everything to string to avoid curl notices.
          'contents' => (string) $value,
        ];
      }
    }

    // Avoid double slashes in final URL.
    $uri = ltrim($uri, "/");
    $options['retries'] = 11;

    $guzzle_response = $this->httpClient->request($method, $this->baseUrl . '/' . $uri, $options);
    $response_body = (string) $guzzle_response->getBody();

    // Catch all errors from Smartling and throw appropriate exception.
    if ($guzzle_response->getStatusCode() >= 400) {
      $error_response = json_decode($response_body, TRUE);

      if (!$error_response || empty($error_response['response']['messages'])) {
        throw new SmartlingApiException('Bad response format from Smartling');
      }

      throw new SmartlingApiException(implode(' || ', $error_response['response']['messages']), $guzzle_response->getStatusCode());
    }

    // "Download file" method return translated file directly.
    if ('file/get' === $uri) {
      return $response_body;
    }

    $response = json_decode($response_body, TRUE);
    // Throw exception if json is not valid.
    if (!$response || empty($response['response']['data'])) {
      throw new SmartlingApiException('Bad response format from Smartling');
    }

    return $response['response']['data'];
  }
}
