<?php

namespace Smartling\Tests;

use Smartling\File\FileApi;
use Smartling\File\Params\DownloadFileParameters;
use Smartling\File\Params\UploadFileParameters;

/**
 * Test class for Smartling\File\FileApi.
 */
class SmartlingApiTest extends \PHPUnit_Framework_TestCase {

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject|\Smartling\File\FileApi
	 */
	protected $object;

	/**
	 * @var string
	 */
	protected $userIdentifier = 'SomeUserIdentifier';

	/**
	 * @var string
	 */
	protected $secretKey = 'SomeSecretKey';

	/**
	 * @var string
	 */
	protected $projectId = 'SomeProjectId';

	/**
	 * @var string
	 */
	protected $validResponse = '{"response":{"data":{"wordCount":1629,"stringCount":503,"overWritten":false},"code":"SUCCESS","messages":[]}}';

	/**
	 * @var string
	 */
	protected $responseWithException = '{"response":{"data":null,"code":"VALIDATION_ERROR","errors":[{"message":"Validation error text"}]}}';

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject|\GuzzleHttp\ClientInterface
	 */
	protected $client;

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject|\Smartling\AuthApi\AuthApiInterface
	 */
	protected $authProvider;

	/**
	 * @var string
	 */
	protected $streamPlaceholder = 'stream';

	/**
	 * @var \PHPUnit_Framework_MockObject_MockObject|\Psr\Http\Message\StreamInterface
	 */
	protected $responseMock;

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp () {
		$this->client = $this->getMockBuilder( 'GuzzleHttp\\ClientInterface' )
		                     ->setMethods( [ 'request', 'send', 'sendAsync', 'requestAsync', 'getConfig' ] )
		                     ->disableOriginalConstructor()
		                     ->getMock();


		$this->authProvider = $this->getMockBuilder( '\Smartling\AuthApi\AuthApiInterface' )
		                           ->setMethods( [ 'getAccessToken', 'getTokenType', 'resetToken' ] )
		                           ->setConstructorArgs( [ $this->userIdentifier, $this->secretKey, $this->client ] )
		                           ->getMock();

		$this->authProvider->expects( self::any() )->method( 'getAccessToken' )->willReturn( 'fakeToken' );
		$this->authProvider->expects( self::any() )->method( 'getTokenType' )->willReturn( 'Bearer' );
		$this->authProvider->expects( self::any() )->method( 'resetToken' );


		$this->responseMock = $this->getMockBuilder( 'Psr\\Http\\Message\\ResponseInterface' )
		                           ->disableOriginalConstructor()
		                           ->getMock();

		$this->responseMock->expects( self::any() )
		                   ->method( 'getBody' )
		                   ->willReturn( $this->validResponse );

		$this->object = $this->getMockBuilder( 'Smartling\\File\FileApi' )
		                     ->setMethods( [ 'readFile' ] )
		                     ->setConstructorArgs( [
			                     $this->projectId,
			                     $this->client,
			                     null,
			                     FileApi::ENDPOINT_URL,
		                     ] )
		                     ->getMock();

		$this->object->expects( self::any() )
		             ->method( 'readFile' )
		             ->willReturn( $this->streamPlaceholder );
	}

	/**
	 * Invokes protected or private method of given object.
	 *
	 * @param FileApi $object
	 *   Object with protected or private method to invoke.
	 * @param string  $methodName
	 *   Name of the property to invoke.
	 * @param array   $parameters
	 *   Array of parameters to be passed to invoking method.
	 *
	 * @return mixed
	 *   Value invoked method will return or exception.
	 */
	protected function invokeMethod ( $object, $methodName, array $parameters = [ ] ) {
		$reflection = new \ReflectionClass( get_class( $object ) );
		$method     = $reflection->getMethod( $methodName );
		$method->setAccessible( true );

		return $method->invokeArgs( $object, $parameters );
	}

	/**
	 * Reads protected or private property of given object.
	 *
	 * @param FileApi $object
	 *   Object with protected or private property.
	 * @param string  $propertyName
	 *   Name of the property to access.
	 *
	 * @return mixed
	 *   Value of read property.
	 */
	protected function readProperty ( $object, $propertyName ) {
		$reflection = new \ReflectionClass( get_class( $object ) );
		$property   = $reflection->getProperty( $propertyName );
		$property->setAccessible( true );

		return $property->getValue( $object );
	}

	/**
	 * Tests constructor.
	 *
	 * @param string                      $projectId
	 *   Project Id string.
	 * @param \GuzzleHttp\ClientInterface $client
	 *   Mock of Guzzle http client instance.
	 * @param string|null                 $expected_base_url
	 *   Base Url string that will be used as based url.
	 *
	 * @covers       \Smartling\File\FileApi::__construct
	 *
	 * @dataProvider constructorDataProvider
	 */
	public function testConstructor ( $projectId, $client, $expected_base_url ) {

		$fileApi = new FileApi( $projectId, $client, null, $expected_base_url );

		self::assertEquals( rtrim( $expected_base_url, '/' ) . '/' . $projectId, $this->invokeMethod( $fileApi, 'getBaseUrl' ) );
		self::assertEquals( $projectId, $this->invokeMethod( $fileApi, 'getProjectId' ) );
		self::assertEquals( $client, $this->invokeMethod( $fileApi, 'getHttpClient' ) );
	}

	/**
	 * Data provider for testConstructor method.
	 *
	 * Tests if base url will be set correctly depending on income baseurl
	 * and mode.
	 *
	 * @return array
	 */
	public function constructorDataProvider () {
		$mockedClient = $this->getMockBuilder( 'GuzzleHttp\\ClientInterface' )
		                     ->setMethods( [ 'request', 'send', 'sendAsync', 'requestAsync', 'getConfig' ] )
		                     ->disableOriginalConstructor()
		                     ->getMock();

		return [
			[ 'product-id', $mockedClient, FileApi::ENDPOINT_URL ],
			[ 'product-id', $mockedClient, FileApi::ENDPOINT_URL ],
			[ 'product-id', $mockedClient, FileApi::ENDPOINT_URL . '/' ],
			[ 'product-id', $mockedClient, 'https://www.google.com.ua/webhp' ],
		];
	}

	/**
	 * @covers \Smartling\File\FileApi::uploadFile
	 */
	public function testUploadFile () {
		$this->client->expects( self::once() )
		             ->method( 'request' )
		             ->with( 'POST', FileApi::ENDPOINT_URL . '/' . $this->projectId . '/file', [
			             'headers'     => [
				             'Accept'        => 'application/json',
				             'Authorization' => vsprintf( ' %s %s', [
					             $this->authProvider->getTokenType(),
					             $this->authProvider->getAccessToken(),
				             ] ),
			             ],
			             'http_errors' => false,
			             'multipart'   => [
				             [
					             'name'     => 'file',
					             'contents' => $this->streamPlaceholder,
				             ],
				             [
					             'name'     => 'smartling.client_lib_id',
					             'contents' =>
						             json_encode(
							             [
								             'client'  => UploadFileParameters::CLIENT_LIB_ID_SDK,
								             'version' => UploadFileParameters::CLIENT_LIB_ID_VERSION,
							             ],
							             JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE
						             ),
				             ],
				             [
					             'name'     => 'authorize',
					             'contents' => '0',
				             ],
				             [
					             'name'     => 'localeIdsToAuthorize[]',
					             'contents' => 'es',
				             ],
				             [
					             'name'     => 'fileUri',
					             'contents' => 'test.xml',
				             ],
				             [
					             'name'     => 'fileType',
					             'contents' => 'xml',
				             ],

			             ],
		             ] )
		             ->willReturn( $this->responseMock );

		$params = new UploadFileParameters();
		$params->setAuthorized( true );
		$params->setLocalesToApprove( [ 'es' ] );

		$this->invokeMethod( $this->object, 'setAuth', [ $this->authProvider ] );

		$this->object->uploadFile( 'tests/resources/test.xml', 'test.xml', 'xml', $params );
	}

	/**
	 * @covers       \Smartling\File\FileApi::downloadFile
	 *
	 * @dataProvider downloadFileParams
	 *
	 * @param DownloadFileParameters|null $options
	 * @param string                      $expected_translated_file
	 */
	public function testDownloadFile ( $options, $expected_translated_file ) {
		$this->responseMock = $this->getMockBuilder( 'Psr\\Http\\Message\\ResponseInterface' )
		                           ->disableOriginalConstructor()
		                           ->getMock();

		$this->responseMock->expects( self::any() )
		                   ->method( 'getBody' )
		                   ->willReturn( $expected_translated_file );

		$endpointUrl = vsprintf( '%s/%s/locales/%s/file', [ FileApi::ENDPOINT_URL, $this->projectId, 'en-EN' ] );


		$params = $options instanceof DownloadFileParameters
			? $options->exportToArray()
			: [ ];

		$params['fileUri'] = 'test.xml';

		$this->client->expects( self::once() )
		             ->method( 'request' )
		             ->with( 'GET', $endpointUrl, [
			             'headers'     => [
				             'Authorization' => vsprintf( ' %s %s', [
					             $this->authProvider->getTokenType(),
					             $this->authProvider->getAccessToken(),
				             ] ),
			             ],
			             'http_errors' => false,
			             'query'       => $params,
		             ] )
		             ->willReturn( $this->responseMock );

		$this->invokeMethod( $this->object, 'setAuth', [ $this->authProvider ] );

		$actual_xml = $this->object->downloadFile( 'test.xml', 'en-EN', $options );

		self::assertEquals( $expected_translated_file, $actual_xml );
	}

	public function downloadFileParams () {
		return [
			[
				( new DownloadFileParameters() )->setRetrievalType( DownloadFileParameters::RETRIEVAL_TYPE_PSEUDO ),
				'<?xml version="1.0"?><response><item key="6"></item></response>',
			],
			[ null, '<?xml version="1.0"?><response><item key="6"></item></response>' ],
			[ null, '{"string1":"translation1", "string2":"translation2"}' ],
		];
	}

	/**
	 * @covers \Smartling\File\FileApi::getStatus
	 */
	public function testGetStatus () {

		$endpointUrl = vsprintf( '%s/%s/locales/%s/file/status', [ FileApi::ENDPOINT_URL, $this->projectId, 'en-EN' ] );

		$this->client->expects( self::once() )
		             ->method( 'request' )
		             ->with( 'GET', $endpointUrl, [
			             'headers'     => [
				             'Accept'        => 'application/json',
				             'Authorization' => vsprintf( ' %s %s', [
					             $this->authProvider->getTokenType(),
					             $this->authProvider->getAccessToken(),
				             ] ),
			             ],
			             'http_errors' => false,
			             'query'       => [
				             'fileUri' => 'test.xml',
			             ],
		             ] )
		             ->willReturn( $this->responseMock );

		$this->invokeMethod( $this->object, 'setAuth', [ $this->authProvider ] );

		$this->object->getStatus( 'test.xml', 'en-EN' );
	}

	/**
	 * @covers \Smartling\File\FileApi::getList
	 */
	public function testGetList () {

		$endpointUrl = vsprintf( '%s/%s/files/list', [ FileApi::ENDPOINT_URL, $this->projectId ] );

		$this->client->expects( self::once() )
		             ->method( 'request' )
		             ->with( 'GET', $endpointUrl, [
			             'headers'     => [
				             'Accept'        => 'application/json',
				             'Authorization' => vsprintf( ' %s %s', [
					             $this->authProvider->getTokenType(),
					             $this->authProvider->getAccessToken(),
				             ] ),
			             ],
			             'http_errors' => false,
			             'query'       => [ ],
		             ] )
		             ->willReturn( $this->responseMock );

		$this->invokeMethod( $this->object, 'setAuth', [ $this->authProvider ] );

		$this->object->getList();
	}

	/**
	 * @covers \Smartling\File\FileApi::sendRequest
	 * @expectedException \Smartling\Exceptions\SmartlingApiException
	 * @expectedExceptionMessage Validation error text
	 */
	public function testValidationErrorSendRequest () {
		$response = $this->getMockBuilder( 'Psr\\Http\\Message\\ResponseInterface' )
		                 ->disableOriginalConstructor()
		                 ->getMock();

		$response->expects( self::any() )
		         ->method( 'getStatusCode' )
		         ->willReturn( 400 );
		$response->expects( self::any() )
		         ->method( 'getBody' )
		         ->willReturn( $this->responseWithException );

		$endpointUrl = vsprintf( '%s/%s/context/html', [ FileApi::ENDPOINT_URL, $this->projectId ] );

		$this->client->expects( self::once() )
		             ->method( 'request' )
		             ->with( 'GET', $endpointUrl, [
			             'headers'     => [
				             'Accept'        => 'application/json',
				             'Authorization' => vsprintf( ' %s %s', [
					             $this->authProvider->getTokenType(),
					             $this->authProvider->getAccessToken(),
				             ] ),
			             ],
			             'http_errors' => false,
			             'query'       => [ ],
		             ] )
		             ->willReturn( $response );

		$this->invokeMethod( $this->object, 'setAuth', [ $this->authProvider ] );
		$this->invokeMethod( $this->object, 'setBaseUrl', [ FileApi::ENDPOINT_URL . '/' . $this->projectId ] );

		$this->invokeMethod( $this->object, 'sendRequest', [ 'context/html', [ ], 'GET' ] );
	}

	/**
	 * @covers \Smartling\File\FileApi::sendRequest
	 * @expectedException \Smartling\Exceptions\SmartlingApiException
	 * @expectedExceptionMessage Bad response format from Smartling
	 */
	public function testBadJsonFormatSendRequest () {
		$response = $this->getMockBuilder( 'Psr\\Http\\Message\\ResponseInterface' )
		                 ->disableOriginalConstructor()
		                 ->getMock();

		$response->expects( self::any() )
		         ->method( 'getStatusCode' )
		         ->willReturn( 200 );
		$response->expects( self::any() )
		         ->method( 'getBody' )
		         ->willReturn( rtrim( $this->responseWithException, '}' ) );

		$endpointUrl = vsprintf( '%s/%s/context/html', [ FileApi::ENDPOINT_URL, $this->projectId ] );

		$this->client->expects( self::once() )
		             ->method( 'request' )
		             ->with( 'GET', $endpointUrl, [
			             'headers'     => [
				             'Accept'        => 'application/json',
				             'Authorization' => vsprintf( ' %s %s', [
					             $this->authProvider->getTokenType(),
					             $this->authProvider->getAccessToken(),
				             ] ),
			             ],
			             'http_errors' => false,
			             'query'       => [ ],
		             ] )
		             ->willReturn( $response );

		$this->invokeMethod( $this->object, 'setAuth', [ $this->authProvider ] );
		$this->invokeMethod( $this->object, 'setBaseUrl', [ FileApi::ENDPOINT_URL . '/' . $this->projectId ] );

		$this->invokeMethod( $this->object, 'sendRequest', [ 'context/html', [ ], 'GET' ] );
	}

	/**
	 * @covers \Smartling\File\FileApi::sendRequest
	 * @expectedException \Smartling\Exceptions\SmartlingApiException
	 * @expectedExceptionMessage Bad response format from Smartling
	 */
	public function testBadJsonFormatInErrorMessageSendRequest () {
		$response = $this->getMockBuilder( 'Psr\\Http\\Message\\ResponseInterface' )
		                 ->disableOriginalConstructor()
		                 ->getMock();

		$response->expects( self::any() )
		         ->method( 'getStatusCode' )
		         ->willReturn( 401 );
		$response->expects( self::any() )
		         ->method( 'getBody' )
		         ->willReturn( rtrim( $this->responseWithException, '}' ) );

		$endpointUrl = vsprintf( '%s/%s/context/html', [ FileApi::ENDPOINT_URL, $this->projectId ] );

		$this->client->expects( self::once() )
		             ->method( 'request' )
		             ->with( 'GET', $endpointUrl, [
			             'headers'     => [
				             'Accept'        => 'application/json',
				             'Authorization' => vsprintf( ' %s %s', [
					             $this->authProvider->getTokenType(),
					             $this->authProvider->getAccessToken(),
				             ] ),
			             ],
			             'http_errors' => false,
			             'query'       => [ ],
		             ] )
		             ->willReturn( $response );

		$this->invokeMethod( $this->object, 'setAuth', [ $this->authProvider ] );
		$this->invokeMethod( $this->object, 'setBaseUrl', [ FileApi::ENDPOINT_URL . '/' . $this->projectId ] );

		$this->invokeMethod( $this->object, 'sendRequest', [ 'context/html', [ ], 'GET' ] );
	}

	/**
	 * @param string $uri
	 * @param array  $requestData
	 * @param string $method
	 * @param array  $params
	 *
	 * @covers       \Smartling\File\FileApi::sendRequest
	 * @dataProvider sendRequestValidProvider
	 */
	public function testSendRequest ( $uri, $requestData, $method, $params ) {

		$params['headers']['Authorization'] = vsprintf( ' %s %s', [
			$this->authProvider->getTokenType(),
			$this->authProvider->getAccessToken(),
		] );

		$this->client->expects( self::once() )
		             ->method( 'request' )
		             ->with( $method, FileApi::ENDPOINT_URL . '/' . $this->projectId . '/' . $uri, $params )
		             ->willReturn( $this->responseMock );

		$this->invokeMethod( $this->object, 'setAuth', [ $this->authProvider ] );
		$this->invokeMethod( $this->object, 'setBaseUrl', [ FileApi::ENDPOINT_URL . '/' . $this->projectId ] );

		$result = $this->invokeMethod( $this->object, 'sendRequest', [ $uri, $requestData, $method ] );
		self::assertEquals( [ 'wordCount' => 1629, 'stringCount' => 503, 'overWritten' => false ], $result );
	}

	/**
	 * Data provider callback for testSendRequest method.
	 *
	 * @return array
	 */
	public function sendRequestValidProvider () {
		return [
			[
				'uri',
				[ ],
				'GET',
				[
					'headers'     => [
						'Accept' => 'application/json',
					],
					'http_errors' => false,
					'query'       => [ ],
				],
			],
			[
				'uri',
				[
					'key'           => 'value',
					'boolean_false' => false,
					'boolean_true'  => true,
					'file'          => './tests/resources/test.xml',
				],
				'POST',
				[
					'headers'     => [
						'Accept' => 'application/json',
					],
					'http_errors' => false,
					'multipart'   => [
						[
							'name'     => 'file',
							'contents' => $this->streamPlaceholder,
						],
						[
							'name'     => 'key',
							'contents' => 'value',
						],
						[
							'name'     => 'boolean_false',
							'contents' => '0',
						],
						[
							'name'     => 'boolean_true',
							'contents' => '1',
						],

					],
				],
			],
		];
	}

	/**
	 * @covers \Smartling\File\FileApi::renameFile
	 */
	public function testRenameFile () {

		$endpointUrl = vsprintf( '%s/%s/file/rename', [ FileApi::ENDPOINT_URL, $this->projectId ] );

		$this->client->expects( self::once() )
		             ->method( 'request' )
		             ->with( 'POST', $endpointUrl, [
			             'headers'     => [
				             'Accept'        => 'application/json',
				             'Authorization' => vsprintf( ' %s %s', [
					             $this->authProvider->getTokenType(),
					             $this->authProvider->getAccessToken(),
				             ] ),
			             ],
			             'http_errors' => false,
			             'multipart'   => [
				             [
					             'name'     => 'fileUri',
					             'contents' => 'test.xml',
				             ],
				             [
					             'name'     => 'newFileUri',
					             'contents' => 'new_test.xml',
				             ],
			             ],
		             ] )
		             ->willReturn( $this->responseMock );

		$this->invokeMethod( $this->object, 'setAuth', [ $this->authProvider ] );

		$this->object->renameFile( 'test.xml', 'new_test.xml' );
	}

	/**
	 * @covers \Smartling\File\FileApi::getAuthorizedLocales
	 */
	public function testGetAuthorizedLocales () {
		$endpointUrl = vsprintf( '%s/%s/file/authorized-locales', [ FileApi::ENDPOINT_URL, $this->projectId ] );

		$this->client->expects( self::once() )
		             ->method( 'request' )
		             ->with( 'GET', $endpointUrl, [
			             'headers'     => [
				             'Accept'        => 'application/json',
				             'Authorization' => vsprintf( ' %s %s', [
					             $this->authProvider->getTokenType(),
					             $this->authProvider->getAccessToken(),
				             ] ),
			             ],
			             'http_errors' => false,
			             'query'       => [
				             'fileUri' => 'test.xml',
			             ],
		             ] )
		             ->willReturn( $this->responseMock );

		$this->invokeMethod( $this->object, 'setAuth', [ $this->authProvider ] );

		$this->object->getAuthorizedLocales( 'test.xml' );
	}

	/**
	 * @covers \Smartling\File\FileApi::deleteFile
	 */
	public function testDeleteFile () {

		$endpointUrl = vsprintf( '%s/%s/file/delete', [ FileApi::ENDPOINT_URL, $this->projectId ] );

		$this->client->expects( self::once() )
		             ->method( 'request' )
		             ->with( 'POST', $endpointUrl, [
			             'headers'     => [
				             'Accept'        => 'application/json',
				             'Authorization' => vsprintf( ' %s %s', [
					             $this->authProvider->getTokenType(),
					             $this->authProvider->getAccessToken(),
				             ] ),
			             ],
			             'http_errors' => false,
			             'multipart'   => [
				             [
					             'name'     => 'fileUri',
					             'contents' => 'test.xml',
				             ],
			             ],
		             ] )
		             ->willReturn( $this->responseMock );

		$this->invokeMethod( $this->object, 'setAuth', [ $this->authProvider ] );

		$this->object->deleteFile( 'test.xml' );
	}

	/**
	 * @covers \Smartling\File\FileApi::import
	 */
	public function testImport () {

		$locale      = 'en-EN';
		$endpointUrl = vsprintf( '%s/%s/locales/%s/file/import', [ FileApi::ENDPOINT_URL, $this->projectId, $locale ] );


		$this->client->expects( self::once() )
		             ->method( 'request' )
		             ->with( 'POST', $endpointUrl, [
			             'headers'     => [
				             'Accept'        => 'application/json',
				             'Authorization' => vsprintf( ' %s %s', [
					             $this->authProvider->getTokenType(),
					             $this->authProvider->getAccessToken(),
				             ] ),
			             ],
			             'http_errors' => false,
			             'multipart'   => [
				             [
					             'name'     => 'file',
					             'contents' => $this->streamPlaceholder,
				             ],
				             /*[
					             'name'     => 'smartling.client_lib_id',
					             'contents' =>
						             json_encode(
							             [
								             'client'  => UploadFileParameters::CLIENT_LIB_ID_SDK,
								             'version' => UploadFileParameters::CLIENT_LIB_ID_VERSION,
							             ],
							             JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE
						             ),
				             ],*/

				             [
					             'name'     => 'fileUri',
					             'contents' => 'test.xml',
				             ],
				             [
					             'name'     => 'fileType',
					             'contents' => 'xml',
				             ],
				             [
					             'name'     => 'translationState',
					             'contents' => 'PUBLISHED',
				             ],
				             [
					             'name'     => 'overwrite',
					             'contents' => '0',
				             ],
			             ],
		             ] )
		             ->willReturn( $this->responseMock );

		$this->invokeMethod( $this->object, 'setAuth', [ $this->authProvider ] );

		$this->object->import( $locale, 'test.xml', 'xml', 'tests/resources/test.xml', 'PUBLISHED', false );
	}

	/**
	 * @covers \Smartling\File\FileApi::readFile
	 */
	public function testReadFile () {

		$validFilePath = './tests/resources/test.xml';

		/**
		 * @var \PHPUnit_Framework_MockObject_MockObject|\Smartling\File\FileApi
		 */
		$fileApi = $this->getMockBuilder( 'Smartling\\File\\FileApi' )
		                ->setConstructorArgs( [ $this->projectId, $this->client ] )
		                ->getMock();

		$stream = $this->invokeMethod( $fileApi, 'readFile', [ $validFilePath ] );

		self::assertEquals( 'stream', get_resource_type( $stream ) );
	}

	/**
	 * @covers \Smartling\File\FileApi::readFile
	 *
	 * @expectedException \Smartling\Exceptions\SmartlingApiException
	 * @expectedExceptionMessage File unexisted was not able to be read.
	 */
	public function testFailedReadFile () {
		$invalidFilePath = 'unexisted';

		/**
		 * @var \PHPUnit_Framework_MockObject_MockObject|\Smartling\File\FileApi
		 */
		$fileApi = $this->getMockBuilder( 'Smartling\\File\\FileApi' )
		                ->setConstructorArgs( [ $this->projectId, $this->client ] )
		                ->getMock();

		$stream = $this->invokeMethod( $fileApi, 'readFile', [ $invalidFilePath ] );

		self::assertEquals( 'stream', get_resource_type( $stream ) );
	}
}
