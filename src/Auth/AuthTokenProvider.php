<?php

namespace Smartling\Auth;

use GuzzleHttp\ClientInterface;
use Smartling\Logger\DevNullLogger;
use Smartling\Logger\LoggerInterface;
use GuzzleHttp\Client;
use Smartling\Exceptions\SmartlingApiException;

class AuthTokenProvider implements AuthApiInterface {
  protected $data;

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
    $client = new Client(['base_uri' => self::$apiUrl, 'debug' => FALSE]);
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
    $this->data = $this->sendRequest('authenticate', [], 'POST');
    /*
     ["accessToken"]=>  string(1087) "eyJhbGciOiJ..."
     ["refreshToken"]=>  string(821) "eyJh..."
     ["expiresIn"]=>  int(300)
     ["refreshExpiresIn"]=>  int(3660)
     ["tokenType"]=>  string(6) "Bearer"
     */
    return $this->data['accessToken'];
  }

  public function getTokenType() {
    return isset($this->data['tokenType']) ? $this->data['tokenType'] : '';
  }

  public function resetToken() {
    $this->data = [];
  }


  protected function sendRequest($uri, array $requestData, $method) {
    // Set api key and product id as required arguments.
    $requestData['userIdentifier'] = $this->userIdentifier;
    $requestData['userSecret'] = $this->secretKey;

    // Ask for JSON and disable Guzzle exceptions.
    $options = [
      'headers' => [
        'Accept' => 'application/json',
      ],
      //@todo: google if we need this
      'http_errors' => FALSE,
    ];

    // For GET and DELETE http methods use just query parameter builder.
    if (in_array($method, ['GET', 'DELETE'])) {
      $options['query'] = $requestData;
    } else {
      $options['json'] = $requestData;
//      $options['multipart'] = [];
//
//      foreach ($requestData as $key => $value) {
//        // Hack to cast FALSE to '0' instead of empty string.
//        if (is_bool($value)) {
//          $value = (int)$value;
//        }
//        $options['multipart'][] = [
//          'name' => $key,
//          // Typecast everything to string to avoid curl notices.
//          'contents' => (string) $value,
//        ];
//      }
    }

    // Avoid double slashes in final URL.
    $uri = ltrim($uri, "/");
//    $options['retries'] = 11;

    $guzzle_response = $this->httpClient->request($method, self::$apiUrl . $uri, $options);
    $response_body = (string) $guzzle_response->getBody();

    // Catch all errors from Smartling and throw appropriate exception.
    if ($guzzle_response->getStatusCode() >= 400) {
      $error_response = json_decode($response_body, TRUE);

      if (!$error_response || empty($error_response['response']['errors'])) {
        throw new SmartlingApiException('Bad response format from Smartling');
      }

      throw new SmartlingApiException(implode(' || ', $error_response['response']['errors']), $guzzle_response->getStatusCode());
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
