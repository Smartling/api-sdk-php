<?php

namespace Smartling\Auth;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\Helpers\HttpVerbHelper;

class AuthTokenProvider implements AuthApiInterface {

	/**
	 * @var array
	 */
	private $data;

	private static $apiUrl = 'https://api.smartling.com/auth-api/v2/';

	/**
	 * @var string
	 */
	private $userIdentifier;

	/**
	 * @var string
	 */
	private $secretKey;

	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var ClientInterface
	 */
	private $httpClient;

	/**
	 * @return string
	 */
	private function getUserIdentifier () {
		return $this->userIdentifier;
	}

	/**
	 * @param string $userIdentifier
	 */
	private function setUserIdentifier ( $userIdentifier ) {
		$this->userIdentifier = $userIdentifier;
	}

	/**
	 * @return string
	 */
	private function getSecretKey () {
		return $this->secretKey;
	}

	/**
	 * @param string $secretKey
	 */
	private function setSecretKey ( $secretKey ) {
		$this->secretKey = $secretKey;
	}

	/**
	 * @return LoggerInterface
	 */
	private function getLogger () {
		return $this->logger;
	}

	/**
	 * @param LoggerInterface $logger
	 */
	private function setLogger ( $logger ) {
		$this->logger = $logger;
	}

	/**
	 * @return ClientInterface
	 */
	private function getHttpClient () {
		return $this->httpClient;
	}

	/**
	 * @param ClientInterface $httpClient
	 */
	private function setHttpClient ( $httpClient ) {
		$this->httpClient = $httpClient;
	}

	/**
	 * AuthTokenProvider constructor.
	 *
	 * @param string          $userIdentifier
	 * @param string          $secretKey
	 * @param ClientInterface $client
	 * @param LoggerInterface $logger
	 */
	public function __construct ( $userIdentifier, $secretKey, ClientInterface $client, LoggerInterface $logger ) {
		$this->setUserIdentifier( $userIdentifier );
		$this->setSecretKey( $secretKey );
		$this->setLogger( $logger );
		$this->setHttpClient( $client );
	}

	/**
	 * Creates and returns instance of AuthTokenProvider
	 *
	 * @param string          $userIdentifier
	 * @param string          $secretKey
	 * @param LoggerInterface $logger
	 *
	 * @return AuthTokenProvider
	 */
	public static function create ( $userIdentifier, $secretKey, LoggerInterface $logger ) {

		$client = new Client(
			[
				'base_uri' => self::$apiUrl,
				'debug'    => false,
			]
		);

		return new self( $userIdentifier, $secretKey, $client, $logger );
	}


	/**
	 * @inheritdoc
	 */
	public function getAccessToken () {
		$this->data = $this->sendRequest( 'authenticate', [ ], HttpVerbHelper::HTTP_VERB_POST );

		return $this->data['accessToken'];
	}

	/**
	 * @inheritdoc
	 */
	public function getTokenType () {
		return isset( $this->data['tokenType'] ) ? $this->data['tokenType'] : '';
	}

	/**
	 * @inheritdoc
	 */
	public function resetToken () {
		$this->data = [ ];
	}

	protected function sendRequest ( $uri, array $requestData, $method ) {
		// Set api key and product id as required arguments.
		$requestData['userIdentifier'] = $this->getUserIdentifier();
		$requestData['userSecret']     = $this->getSecretKey();

		// Ask for JSON and disable Guzzle exceptions.
		$options = [
			'headers'     => [
				'Accept' => 'application/json',
			],
			//@todo: google if we need this
			'http_errors' => false,
		];

		// For GET and DELETE http methods use just query parameter builder.
		if ( in_array( $method, [ HttpVerbHelper::HTTP_VERB_GET, HttpVerbHelper::HTTP_VERB_DELETE ] ) ) {
			$options['query'] = $requestData;
		} else {
			$options['json'] = $requestData;
		}

		// Avoid double slashes in final URL.
		$uri = ltrim( $uri, "/" );

		$guzzle_response = $this->getHttpClient()->request( $method, self::$apiUrl . $uri, $options );
		$response_body   = (string) $guzzle_response->getBody();

		// Catch all errors from Smartling and throw appropriate exception.
		if ( 400 <= $guzzle_response->getStatusCode() ) {
			$error_response = json_decode( $response_body, true );

			if ( ! $error_response || empty( $error_response['response']['errors'] ) ) {
				throw new SmartlingApiException( 'Bad response format from Smartling' );
			}

			$message = vsprintf('Authorization Error. Code: %s',[$error_response['response']['code']]);

			$this->getLogger()->warning($message);

			throw new SmartlingApiException( $message, $guzzle_response->getStatusCode() );
		}

		$response = json_decode( $response_body, true );

		// Throw exception if json is not valid.
		if ( ! $response || empty( $response['response']['data'] ) ) {
			throw new SmartlingApiException( 'Bad response format from Smartling' );
		}

		return $response['response']['data'];
	}
}
