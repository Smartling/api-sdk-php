<?php

namespace Smartling;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\Helpers\HttpVerbHelper;
use Smartling\Logger\DevNullLogger;

/**
 * Class BaseApiAbstract
 *
 * @package Smartling\Api
 */
abstract class BaseApiAbstract {

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
	protected function getProjectId () {
		return $this->projectId;
	}

	/**
	 * @param string $projectId
	 */
	protected function setProjectId ( $projectId ) {
		$this->projectId = $projectId;
	}

	/**
	 * @return string
	 */
	protected function getBaseUrl () {
		return $this->baseUrl;
	}

	/**
	 * @param string $baseUrl
	 */
	protected function setBaseUrl ( $baseUrl ) {
		$this->baseUrl = $baseUrl;
	}

	/**
	 * @return AuthApiInterface
	 */
	protected function getAuth () {
		return $this->auth;
	}

	/**
	 * @param AuthApiInterface $auth
	 */
	protected function setAuth ( AuthApiInterface $auth ) {
		$this->auth = $auth;
	}

	/**
	 * @return ClientInterface
	 */
	protected function getHttpClient () {
		return $this->httpClient;
	}

	/**
	 * @param ClientInterface $httpClient
	 */
	protected function setHttpClient ( $httpClient ) {
		$this->httpClient = $httpClient;
	}

	/**
	 * @return LoggerInterface
	 */
	protected function getLogger () {
		return $this->logger;
	}

	/**
	 * @param LoggerInterface $logger
	 */
	protected function setLogger ( $logger ) {
		$this->logger = $logger;
	}


	/**
	 * BaseApiAbstract constructor.
	 *
	 * @param string          $projectId
	 * @param ClientInterface $client
	 * @param LoggerInterface $logger
	 * @param string|null     $service_url
	 */
	public function __construct ( $projectId, ClientInterface $client, $logger = null, $service_url = null ) {
		$this->setProjectId( $projectId );
		$this->setHttpClient( $client );

		if ( is_null( $logger ) ) {
			$logger = new DevNullLogger();
		}

		$this->setLogger( $logger );

		$this->setBaseUrl( rtrim( $service_url, '/' ) . '/' . $projectId );
	}

	/**
	 * @param string $serviceUrl
	 * @param bool   $debug
	 *
	 * @return Client
	 */
	protected static function initializeHttpClient ( $serviceUrl, $debug = false ) {
		$client = new Client(
			[
				'base_uri' => $serviceUrl,
				'debug'    => $debug,
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
	protected function readFile ( $realPath ) {
		$stream = @fopen( $realPath, 'r' );

		if ( ! $stream ) {
			throw new SmartlingApiException( "File $realPath was not able to be read." );
		} else {
			return $stream;
		}
	}

	/**
	 * @param string $strategy
	 * @param bool   $httpErrors
	 *
	 * @return array
	 */
	private function prepareOptions ( $strategy, $httpErrors = false ) {
		$options = [
			'headers'     => [
				'Accept' => 'application/json',
			],
			'http_errors' => $httpErrors,
		];

		if ( self::STRATEGY_AUTH !== $strategy ) {
			$accessToken                         = $this->getAuth()->getAccessToken();
			$tokenType                           = $this->getAuth()->getTokenType();
			$options['headers']['Authorization'] =
				vsprintf( ' %s %s', [ $tokenType, $accessToken ] );
		}

		if ( self::STRATEGY_DOWNLOAD === $strategy ) {
			unset( $options['headers']['Accept'] );
		}

		return $options;
	}

	/**
	 * @param array $options
	 * @param array $requestData
	 *
	 * @return array
	 */
	private function addRequestDataToOptions ( array $options, array $requestData = [ ] ) {

		foreach ( $requestData as $key => $value ) {
			// Hack to cast FALSE to '0' instead of empty string.
			if ( is_bool( $value ) ) {
				$value = (int) $value;
			}

			if ( is_array( $value ) ) {
				foreach ( $value as $_item ) {
					$options['multipart'][] = [
						'name'     => $key . '[]',
						'contents' => (string) $_item,
					];
				}
			} else {
				$options['multipart'][] = [
					'name'     => $key,
					'contents' => (string) $value,
				];
			}
		}

		return $options;
	}

	/**
	 * @param string $uri
	 *
	 * @return string
	 */
	private function normalizeUri ( $uri = '' ) {
		$endpoint = rtrim( $this->getBaseUrl(), '/' ) . '/' . ltrim( $uri, '/' );

		return $endpoint;
	}

	/**
	 * @param int $responseStatusCode
	 */
	private function checkAuthenticationError ( $responseStatusCode ) {
		//Special handling for 401 error - authentication error => expire token
		if ( 401 === (int) $responseStatusCode ) {
			$this->getAuth()->resetToken();
		}
	}

	/**
	 * @param string $responseStatusCode
	 * @param string $responseBody
	 *
	 * @throws SmartlingApiException
	 */
	private function processErrors ( $responseStatusCode, $responseBody ) {
		// Catch all errors from Smartling and throw appropriate exception.
		if ( $responseStatusCode >= 400 ) {
			$errorResponse = json_decode( $responseBody, true );

			if ( ! $errorResponse
			     || ! is_array( $errorResponse )
			     || ! array_key_exists( 'response', $errorResponse )
			     || ! is_array( $errorResponse['response'] )
			     || ! array_key_exists( 'errors', $errorResponse['response'] )
			     || empty( $errorResponse['response']['errors'] )
			) {
				$message = 'Bad response format from Smartling';
				$this->getLogger()->error( $message );
				throw new SmartlingApiException( $message );
			}

			$error_msg = array_map(
				function ( $a ) {
					return $a['message'];
				},
				$errorResponse['response']['errors']
			);

			$message = implode( ' || ', $error_msg );

			$this->getLogger()->error( $message );
			throw new SmartlingApiException( $message, $responseStatusCode );
		}
	}


	/**
	 * @param string $uri
	 * @param array  $requestData
	 * @param string $method
	 * @param string $strategy
	 *
	 * @return  bool true on SUCCESS and empty data
	 *          string on $processResponseBody = false
	 *          array otherwise
	 * @throws SmartlingApiException
	 */
	protected function sendRequest ( $uri, array $requestData, $method, $strategy = self::STRATEGY_GENERAL ) {

		$options = $this->prepareOptions( $strategy );

		if ( in_array( $method, [ HttpVerbHelper::HTTP_VERB_GET, HttpVerbHelper::HTTP_VERB_DELETE ] ) ) {
			$options['query'] = $requestData;
		} else {
			if ( self::STRATEGY_AUTH === $strategy ) {
				$options['json'] = $requestData;
			} else {
				$options['multipart'] = [ ];

				// Remove file from params array and add it as a stream.
				if ( ! empty( $requestData['file'] ) ) {
					$options['multipart'][] = [
						'name'     => 'file',
						'contents' => $this->readFile( $requestData['file'] ),
					];
					unset( $requestData['file'] );
				}
				$options = $this->addRequestDataToOptions( $options, $requestData );
			}
		}

		$endpoint = $this->normalizeUri( $uri );

		$guzzleResponse = $this->getHttpClient()->request( $method, $endpoint, $options );

		$this->getLogger()->debug(
			json_encode(
				[
					'request'  => [
						'endpoint'    => $endpoint,
						'method'      => $method,
						'requestData' => $options,
					],
					'response' => [
						'statusCode' => $guzzleResponse->getStatusCode(),
						'headers'    => $guzzleResponse->getHeaders(),
						'body'       => (string) $guzzleResponse->getBody(),
					],
				],
				JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT
			)
		);

		$responseBody       = (string) $guzzleResponse->getBody();
		$responseStatusCode = $guzzleResponse->getStatusCode();

		if ( 400 <= $responseStatusCode ) {
			$this->checkAuthenticationError( $responseStatusCode );
			$this->processErrors( $responseStatusCode, $responseBody );
		}

		if ( self::STRATEGY_DOWNLOAD === $strategy ) {
			return $responseBody;
		} else {
			$response = json_decode( $responseBody, true );

			// Throw exception if json is not valid.
			if ( ! $response
			     || ! is_array( $response )
			     || ! array_key_exists( 'response', $response )
			     || ! is_array( $response['response'] )
			     || empty( $response['response']['code'] )
			     || $response['response']['code'] !== 'SUCCESS'
			) {
				$message = 'Bad response format from Smartling';
				$this->getLogger()->error( $message );
				throw new SmartlingApiException( $message );
			}

			return isset( $response['response']['data'] ) ? $response['response']['data'] : true;
		}
	}
}