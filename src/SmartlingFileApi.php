<?php

namespace Smartling;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Smartling\Auth\AuthApiInterface;
use Smartling\Auth\AuthTokenProvider;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\Helpers\HttpVerbHelper;
use Smartling\Params\DownloadFileParameters;
use Smartling\Params\ListFilesParameters;
use Smartling\Params\ParameterInterface;
use Smartling\Params\UploadFileParameters;

class SmartlingFileApi {

	const STRATEGY_GENERAL = 'general';

	const STRATEGY_DOWNLOAD = 'download';

	/**
	 * Default service entry point
	 */
	const DEFAULT_SERVICE_URL = 'https://api.smartling.com/files-api/v2/projects/';

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
	 * Smartling API key.
	 *
	 * @var string
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
	private function getProjectId () {
		return $this->projectId;
	}

	/**
	 * @param string $projectId
	 */
	private function setProjectId ( $projectId ) {
		$this->projectId = $projectId;
	}

	/**
	 * @return string
	 */
	private function getBaseUrl () {
		return $this->baseUrl;
	}

	/**
	 * @param string $baseUrl
	 */
	private function setBaseUrl ( $baseUrl ) {
		$this->baseUrl = $baseUrl;
	}

	/**
	 * @return string
	 */
	private function getAuth () {
		return $this->auth;
	}

	/**
	 * @param string $auth
	 */
	private function setAuth ( $auth ) {
		$this->auth = $auth;
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
	 * SmartlingFileApi constructor.
	 *
	 * @param                  $projectId
	 * @param AuthApiInterface $auth
	 * @param ClientInterface  $client
	 * @param LoggerInterface  $logger
	 * @param null             $base_service_url
	 */
	public function __construct ( $projectId, AuthApiInterface $auth, ClientInterface $client, LoggerInterface $logger, $base_service_url = null ) {
		$this->setProjectId( $projectId );
		$this->setAuth( $auth );
		$this->setHttpClient( $client );
		$this->setLogger( $logger );
		$this->setBaseUrl( rtrim( $base_service_url ? : self::DEFAULT_SERVICE_URL, '/' ) . '/' . $projectId );
	}


	/**
	 * @param string          $projectId
	 * @param string          $userIdentifier
	 * @param string          $secretKey
	 * @param LoggerInterface $logger
	 * @param null|string     $serviceUrl
	 *
	 * @return static
	 */
	public static function create ( $projectId, $userIdentifier, $secretKey, LoggerInterface $logger, $serviceUrl = null ) {
		$client = new Client(
			[
				'base_uri' => self::DEFAULT_SERVICE_URL,
				'debug'    => false,
			]
		);

		$auth = AuthTokenProvider::create( $userIdentifier, $secretKey, $logger );

		return new self( $projectId, $auth, $client, $logger, $serviceUrl );
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
	 * Sends request to Smartling Service via Guzzle Client.
	 *
	 * @param string  $uri
	 *   Resource uri.
	 * @param array   $requestData
	 *   Parameters to be send as query or multipart form elements.
	 * @param string  $method
	 *   Http method uppercased.
	 * @param boolean $processResponseBody
	 *   Tells whether reposnse should be processed as usual or not. The body
	 *   shouldn't be processed when we download a file, and response is an XML.
	 *
	 * @return array
	 *   Decoded JSON answer.
	 *
	 * @throws \Smartling\Exceptions\SmartlingApiException
	 */
	protected function sendRequest ( $uri, array $requestData, $method, $processResponseBody = true, $strategy = self::STRATEGY_GENERAL ) {
		$token     = $this->getAuth()->getAccessToken();
		$tokenType = $this->getAuth()->getTokenType();

		$options = [
			'headers'     => [
				'Accept'        => 'application/json',
				'Authorization' => " $tokenType $token",
			],
			'http_errors' => false,
		];

		if ( self::STRATEGY_DOWNLOAD === $strategy ) {
			unset( $options['headers']['Accept'] );
		}

		if ( in_array( $method, [ HttpVerbHelper::HTTP_VERB_GET, HttpVerbHelper::HTTP_VERB_DELETE ] ) ) {
			$options['query'] = $requestData;
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
			foreach ( $requestData as $key => $value ) {
				// Hack to cast FALSE to '0' instead of empty string.
				if ( is_bool( $value ) ) {
					$value = (int) $value;
				}

				if (is_array($value))
				{
					foreach($value as $_item)
					{
						$options['multipart'][] = [
							'name'     => $key.'[]',
							'contents' => (string) $_item,
						];
					}


				} else
				{

					$options['multipart'][] = [
						'name'     => $key,
						'contents' => (string) $value,
					];
				}
			}
		}

		// Avoid double slashes in final URL.
		$uri = ltrim( $uri, "/" );

		$endpoint = $this->getBaseUrl() . '/' . $uri;

		$guzzle_response = $this->getHttpClient()->request( $method, $endpoint, $options );
		$response_body   = (string) $guzzle_response->getBody();
		$status_code     = $guzzle_response->getStatusCode();

		//Special handling for 401 error - authentication error => expire token
		if ( $status_code == 401 ) {
			$this->getAuth()->resetToken();
		}

		// Catch all errors from Smartling and throw appropriate exception.
		if ( $status_code >= 400 ) {
			$error_response = json_decode( $response_body, true );

			if ( ! $error_response || empty( $error_response['response']['errors'] ) ) {
				throw new SmartlingApiException( 'Bad response format from Smartling' );
			}

			$error_msg = array_map( function ( $a ) {
				return $a['message'];
			}, $error_response['response']['errors'] );
			throw new SmartlingApiException( implode( ' || ', $error_msg ), $guzzle_response->getStatusCode() );
		}

		if ( ! $processResponseBody ) {
			return $response_body;
		}

		$response = json_decode( $response_body, true );
		// Throw exception if json is not valid.
		if ( ! $response || $response['response']['code'] !== 'SUCCESS' ) {
			throw new SmartlingApiException( 'Bad response format from Smartling' );
		}

		return isset( $response['response']['data'] ) ? $response['response']['data'] : true;
	}

	/**
	 * Uploads original source content to Smartling.
	 *
	 * @param string               $realPath
	 *   Real path to the file to read in into stream.
	 * @param string               $file_name
	 *   Value that uniquely identifies the uploaded file. This ID can be used to
	 *   request the file back.
	 * @param string               $file_type
	 *   Unique identifier for the file type. Permitted values: android, ios,
	 *   gettext, html, javaProperties, yaml, xliff, xml, json, docx, pptx, xlsx,
	 *   idml, qt, resx, plaintext, cvs, stringsdict.
	 * @param UploadFileParameters $params
	 *   (optional) An associative array of additional options, with the following
	 *   elements:
	 *   - 'approved': Determines whether content in the file is authorized
	 *     (available for translation) upon submitting the file via the Smartling
	 *     Dashboard.
	 *   - 'localesToApprove': This value, if set, authorizes strings for
	 *     translation into specific locales.
	 *
	 * @return array
	 *   Data about uploaded file.
	 *
	 * @throws \Smartling\Exceptions\SmartlingApiException
	 *
	 * @see http://docs.smartling.com/pages/API/FileAPI/Upload-File/
	 */
	public function uploadFile ( $realPath, $file_name, $file_type, UploadFileParameters $params = null ) {
		$params = ( is_null( $params ) ) ? [ ] : $params->exportToArray();

		$params['file']     = $realPath;
		$params['fileUri']  = $file_name;
		$params['fileType'] = $file_type;

		return $this->sendRequest( 'file', $params, HttpVerbHelper::HTTP_VERB_POST );
	}

	/**
	 * Downloads the requested file from Smartling.
	 *
	 * It is important to check the HTTP response status code. If Smartling finds
	 * and returns the file normally, you will receive a 200 SUCCESS response.
	 * If you receive any other response status code than 200, the requested
	 * file will not be part of the response.
	 *
	 * @param string                 $fileUri
	 *   Value that uniquely identifies the downloaded file.
	 * @param string                 $locale
	 *   A locale identifier as specified in project setup. If no locale
	 *   is specified, original content is returned.
	 * @param DownloadFileParameters $params
	 *   (optional) An associative array of additional options, with the following
	 *   elements:
	 *   - 'retrievalType': Determines the desired format for the download. Could
	 *     be one of following values:
	 *     pending|published|pseudo|contextMatchingInstrumented
	 *   - 'includeOriginalStrings': Boolean that specifies whether Smartling will
	 *     return the original string or an empty string where no translation
	 *     is available.
	 *
	 * @return string
	 *   File content.
	 *
	 * @throws \Smartling\Exceptions\SmartlingApiException
	 *
	 * @see http://docs.smartling.com/pages/API/FileAPI/Download-File/
	 */
	public function downloadFile ( $fileUri, $locale = '', DownloadFileParameters $params = null ) {
		$params            = ( is_null( $params ) ) ? [ ] : $params->exportToArray();
		$params['fileUri'] = $fileUri;

		return $this->sendRequest( "locales/{$locale}/file", $params, HttpVerbHelper::HTTP_VERB_GET, false, self::STRATEGY_DOWNLOAD );
	}

	/**
	 * Retrieves status about file translation progress.
	 *
	 * @param string             $fileUri
	 *   Value that uniquely identifies the file.
	 * @param string             $locale
	 *   A locale identifier as specified in project setup.
	 * @param ParameterInterface $params
	 *   Additional parameters that might be added later
	 *
	 * @return array Data about request file.
	 * Data about request file.
	 * @throws SmartlingApiException
	 * @see http://docs.smartling.com/pages/API/FileAPI/Status/
	 */
	public function getStatus ( $fileUri, $locale, ParameterInterface $params = null ) {
		$params            = ( is_null( $params ) ) ? [ ] : $params->exportToArray();
		$params['fileUri'] = $fileUri;

		return $this->sendRequest( "locales/$locale/file/status", $params, HttpVerbHelper::HTTP_VERB_GET );
	}

	/**
	 * Lists recently uploaded files. Returns a maximum of 500 files.
	 *
	 * @param ListFilesParameters $params
	 *   (optional) An associative array of additional options, with the following
	 *   elements:
	 *   - 'uriMask': Returns only files with a URI containing a given string.
	 *     Case is ignored and % is a wildcard. For example, the value .js%n will
	 *     match strings.json and STRINGS.JSON but not json.strings.
	 *   - 'fileTypes': Identifiers: android, ios, gettext, html, javaProperties,
	 *     yaml, xliff, xml, json, docx, pptx, xlsx, idml, qt, resx, plaintext,
	 *     cvs. File types are combined using the logical "OR".
	 *   - 'lastUploadedAfter': Returns all files uploaded after the specified
	 *     date.
	 *   - 'lastUploadedBefore': Returns all files uploaded before the specified
	 *     date.
	 *   - 'offset': For result set returns, the offset is a number indicating the
	 *     distance from the beginning of the list; for example, for a result set
	 *     of "50" files, you can set the offset at 10 to return files 10 - 50.
	 *   - 'limit': For result set returns, limits the number of files returned;
	 *     for example, for a result set of 50 files, a limit of "10" would
	 *     return files 0 - 10.
	 *
	 * @return array
	 *   List of files objects.
	 *
	 * @throws \Smartling\Exceptions\SmartlingApiException
	 *
	 * @see http://docs.smartling.com/pages/API/FileAPI/List/
	 */
	public function getList ( ListFilesParameters $params = null ) {
		$params = ( is_null( $params ) ) ? [ ] : $params->exportToArray();

		return $this->sendRequest( 'files/list', $params, HttpVerbHelper::HTTP_VERB_GET );
	}

	/**
	 * Renames an uploaded file by changing the fileUri.
	 *
	 * After renaming the file, the file will only be identified by the new
	 * fileUri you provide.
	 *
	 * @param string             $fileUri
	 *   Current value that uniquely identifies the file.
	 * @param string             $newFileUri
	 *   The new value for fileUri. We recommend that you use file path + file
	 *   name, similar to how version control systems identify the file.
	 * @param ParameterInterface $params
	 *
	 * @return string Just empty string if everything was successfully.
	 * Just empty string if everything was successfully.
	 * @throws SmartlingApiException
	 * @see http://docs.smartling.com/pages/API/FileAPI/Rename/
	 */
	public function renameFile ( $fileUri, $newFileUri, ParameterInterface $params = null ) {
		$params               = ( is_null( $params ) ) ? [ ] : $params->exportToArray();
		$params['fileUri']    = $fileUri;
		$params['newFileUri'] = $newFileUri;

		return $this->sendRequest( 'file/rename', $params, HttpVerbHelper::HTTP_VERB_POST );
	}

	/**
	 * Removes the file from Smartling.
	 *
	 * The file will no longer be available for download. Any complete
	 * translations for the file remain available for use within the system.
	 * Smartling deletes files asynchronously and it typically takes a few minutes
	 * to complete. While deleting a file, you can not upload a file with the
	 * same fileUri.
	 *
	 * @param string             $fileUri
	 * @param ParameterInterface $params
	 *
	 * @return array
	 * @throws SmartlingApiException
	 */
	public function deleteFile ( $fileUri, ParameterInterface $params = null ) {
		$params = ( is_null( $params ) ) ? [ ] : $params->exportToArray();

		$params['fileUri'] = $fileUri;

		return $this->sendRequest( 'file/delete', $params, HttpVerbHelper::HTTP_VERB_POST );
	}

	/**
	 * Import files form Service.
	 *
	 * @param string  $locale
	 *   The Smartling locale identifier for the language Smartling is importing.
	 * @param string  $fileUri
	 *   The Smartling URI for file that contains the original language strings
	 *   already uploaded to Smartling.
	 * @param string  $fileType
	 *   The type of file used for imports. Valid values are: ios, android,
	 *   gettext, javaProperties, xml, json, yaml, and csv.
	 * @param string  $fileRealPath
	 *   Absolute path to the file on your local machine that contains the
	 *   translated content,
	 * @param string  $translationState
	 *   Value indicating the workflow state to import the translations into.
	 *   Content will be imported into the language's default workflow.
	 *   Could be 'PUBLISHED' or 'POST_TRANSLATION'.
	 * @param boolean $overwrite
	 *   (optional) An associative array of additional options, with the following
	 *   elements:
	 *   - 'overwrite': Boolean indicating whether or not to overwrite existing
	 *     translations.
	 *
	 * @return string
	 *
	 * @throws \Smartling\Exceptions\SmartlingApiException
	 *
	 * @see http://docs.smartling.com/pages/API/Translation-Imports/
	 */
	public function import ( $locale, $fileUri, $fileType, $fileRealPath, $translationState, $overwrite = false ) {
		$params['fileUri']          = $fileUri;
		$params['fileType']         = $fileType;
		$params['file']             = $fileRealPath;
		$params['translationState'] = $translationState;
		$params['overwrite']        = $overwrite;

		return $this->sendRequest( "/locales/$locale/file/import", $params, HttpVerbHelper::HTTP_VERB_POST );
	}

	/**
	 * Get list of authorized locales for given file.
	 *
	 * @param string             $fileUri
	 * @param ParameterInterface $params
	 *
	 * @return array
	 * @throws SmartlingApiException
	 */
	public function getAuthorizedLocales ( $fileUri, ParameterInterface $params = null ) {
		$params = ( is_null( $params ) ) ? [ ] : $params->exportToArray();

		$params['fileUri'] = $fileUri;

		return $this->sendRequest( 'file/authorized-locales', $params, HttpVerbHelper::HTTP_VERB_GET );
	}

	/**
	 * retrieve all statuses about file translations progress
	 *
	 * @param                    $fileUri
	 * @param ParameterInterface $params
	 *
	 * @return array
	 * @throws SmartlingApiException
	 */
	public function getStatusAllLocales ( $fileUri, ParameterInterface $params = null ) {
		$params = ( is_null( $params ) ) ? [ ] : $params->exportToArray();

		$params['fileUri'] = $fileUri;

		return $this->sendRequest( 'file/status', $params, HttpVerbHelper::HTTP_VERB_GET );
	}


	/**
	 * retrieve all statuses about file translations progress
	 *
	 * @param string             $fileUri
	 * @param ParameterInterface $params
	 *
	 * @return array
	 * @throws SmartlingApiException
	 */
	public function getLastModified ( $fileUri, ParameterInterface $params = null ) {
		$params = ( is_null( $params ) ) ? [ ] : $params->exportToArray();

		$params['fileUri'] = $fileUri;

		return $this->sendRequest( 'file/last-modified', $params, HttpVerbHelper::HTTP_VERB_GET );
	}

	public function getProjectDetails () {
		return $this->sendRequest( '', [], HttpVerbHelper::HTTP_VERB_GET );
	}
}
