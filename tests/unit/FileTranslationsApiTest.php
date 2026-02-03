<?php

namespace Smartling\Tests\Unit;

use Smartling\FileTranslations\FileTranslationsApi;
use Smartling\FileTranslations\Params\TranslateFileParameters;

/**
 * Test class for Smartling\FileTranslations\FileTranslationsApi.
 */
class FileTranslationsApiTest extends ApiTestAbstract
{
    /**
     * @var string
     */
    protected $accountUid = 'test-account-uid';

    /**
     * Prepares FileTranslationsApi mock.
     */
    private function prepareFileTranslationsApiMock()
    {
        $this->object = $this->getMockBuilder('Smartling\FileTranslations\FileTranslationsApi')
            ->setMethods(['readFile'])
            ->setConstructorArgs([
                $this->accountUid,
                $this->client,
                null,
                FileTranslationsApi::ENDPOINT_URL,
            ])
            ->getMock();

        $this->object->expects($this->any())
            ->method('readFile')
            ->willReturn($this->streamPlaceholder);

        $this->invokeMethod(
            $this->object,
            'setAuth',
            [
                $this->authProvider
            ]
        );
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->prepareHttpClientMock();
        $this->prepareAuthProviderMock();
        $this->prepareFileTranslationsApiMock();
    }

    /**
     * Tests constructor.
     *
     * @param string $accountUid
     *   Account UID string.
     * @param \GuzzleHttp\ClientInterface $client
     *   Mock of Guzzle http client instance.
     * @param string|null $expected_base_url
     *   Base Url string that will be used as based url.
     *
     * @covers       \Smartling\FileTranslations\FileTranslationsApi::__construct
     *
     * @dataProvider constructorDataProvider
     */
    public function testConstructor($accountUid, $client, $expected_base_url)
    {
        $this->prepareClientResponseMock();
        $api = new FileTranslationsApi($accountUid, $client, null, $expected_base_url);

        $this->assertEquals(\rtrim($expected_base_url, '/') . '/' . $accountUid,
            $this->invokeMethod($api, 'getBaseUrl'));
        $this->assertEquals($client, $this->invokeMethod($api, 'getHttpClient'));
    }

    /**
     * Data provider for testConstructor method.
     *
     * @return array
     */
    public function constructorDataProvider()
    {
        $this->prepareHttpClientMock();

        $mockedClient = $this->client;

        return [
            ['account-uid-123', $mockedClient, FileTranslationsApi::ENDPOINT_URL],
            ['account-uid-456', $mockedClient, FileTranslationsApi::ENDPOINT_URL . '/'],
        ];
    }

    /**
     * @covers \Smartling\FileTranslations\FileTranslationsApi::uploadFile
     */
    public function testUploadFile()
    {
        $this->prepareClientResponseMock();

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willReturnCallback(function(string $method, string $uri, array $options) {
                $this->assertEquals('post', $method);
                $this->assertEquals(FileTranslationsApi::ENDPOINT_URL . '/' . $this->accountUid . '/files', $uri);

                // Verify headers
                $this->assertEquals('application/json', $options['headers']['Accept']);
                $this->assertStringStartsWith('Bearer', $options['headers']['Authorization']);

                // Verify multipart structure
                $this->assertArrayHasKey('multipart', $options);
                $this->assertIsArray($options['multipart']);

                // Find file and request parts
                $filePart = null;
                $requestPart = null;
                foreach ($options['multipart'] as $part) {
                    if ($part['name'] === 'file') {
                        $filePart = $part;
                    }
                    if ($part['name'] === 'request') {
                        $requestPart = $part;
                    }
                }

                $this->assertNotNull($filePart, 'File part should be present');
                $this->assertNotNull($requestPart, 'Request part should be present');

                // Verify file part
                $this->assertEquals($this->streamPlaceholder, $filePart['contents']);
                $this->assertEquals('test.json', $filePart['filename']);

                // Verify request part
                $requestData = json_decode($requestPart['contents'], true);
                $this->assertEquals('json', $requestData['fileType']);

                return $this->responseMock;
            });

        $this->object->uploadFile('tests/resources/test-fts.json', 'test.json', 'json');
    }

    /**
     * @covers \Smartling\FileTranslations\FileTranslationsApi::translateFile
     */
    public function testTranslateFile()
    {
        $this->prepareClientResponseMock();

        $fileUid = 'file-uid-123';

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willReturnCallback(function(string $method, string $uri, array $options) use ($fileUid) {
                $this->assertEquals('post', $method);
                $this->assertEquals(
                    FileTranslationsApi::ENDPOINT_URL . '/' . $this->accountUid . '/files/' . $fileUid . '/mt',
                    $uri
                );

                // Verify JSON body
                $this->assertArrayHasKey('json', $options);
                $this->assertEquals('en', $options['json']['sourceLocaleId']);
                $this->assertEquals(['es', 'fr', 'de'], $options['json']['targetLocaleIds']);
                $this->assertEquals('https://example.com/callback', $options['json']['callbackUrl']);

                return $this->responseMock;
            });

        $params = new TranslateFileParameters();
        $params->setSourceLocaleId('en')
            ->setTargetLocaleIds(['es', 'fr', 'de'])
            ->setCallbackUrl('https://example.com/callback');

        $this->object->translateFile($fileUid, $params);
    }

    /**
     * @covers \Smartling\FileTranslations\FileTranslationsApi::getTranslationProgress
     */
    public function testGetTranslationProgress()
    {
        $this->prepareClientResponseMock();

        $fileUid = 'file-uid-123';
        $mtUid = 'mt-uid-456';

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willReturnCallback(function(string $method, string $uri, array $options) use ($fileUid, $mtUid) {
                $this->assertEquals('get', $method);
                $this->assertEquals(
                    FileTranslationsApi::ENDPOINT_URL . '/' . $this->accountUid . '/files/' . $fileUid . '/mt/' . $mtUid . '/status',
                    $uri
                );

                return $this->responseMock;
            });

        $this->object->getTranslationProgress($fileUid, $mtUid);
    }

    /**
     * @covers \Smartling\FileTranslations\FileTranslationsApi::downloadTranslatedFile
     */
    public function testDownloadTranslatedFile()
    {
        // Create a mock response with raw content
        $rawContent = '{"translated": "content"}';
        $stream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $stream->method('__toString')->willReturn($rawContent);

        $responseMock = $this->createMock(\GuzzleHttp\Psr7\Response::class);
        $responseMock->method('getBody')->willReturn($stream);
        $responseMock->method('getStatusCode')->willReturn(200);

        $fileUid = 'file-uid-123';
        $mtUid = 'mt-uid-456';
        $localeId = 'es';

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willReturnCallback(function(string $method, string $uri, array $options) use ($fileUid, $mtUid, $localeId, $responseMock) {
                $this->assertEquals('get', $method);
                $this->assertEquals(
                    FileTranslationsApi::ENDPOINT_URL . '/' . $this->accountUid . '/files/' . $fileUid . '/mt/' . $mtUid . '/locales/' . $localeId . '/file',
                    $uri
                );

                // Verify Accept header is removed for raw download
                $this->assertArrayNotHasKey('Accept', $options['headers']);

                return $responseMock;
            });

        $result = $this->object->downloadTranslatedFile($fileUid, $mtUid, $localeId);
        $this->assertEquals($rawContent, $result);
    }

    /**
     * @covers \Smartling\FileTranslations\FileTranslationsApi::downloadAllTranslationsZip
     */
    public function testDownloadAllTranslationsZip()
    {
        // Create a mock response with raw content
        $rawContent = 'ZIP_BINARY_CONTENT';
        $stream = $this->createMock(\Psr\Http\Message\StreamInterface::class);
        $stream->method('__toString')->willReturn($rawContent);

        $responseMock = $this->createMock(\GuzzleHttp\Psr7\Response::class);
        $responseMock->method('getBody')->willReturn($stream);
        $responseMock->method('getStatusCode')->willReturn(200);

        $fileUid = 'file-uid-123';
        $mtUid = 'mt-uid-456';

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willReturnCallback(function(string $method, string $uri, array $options) use ($fileUid, $mtUid, $responseMock) {
                $this->assertEquals('get', $method);
                $this->assertEquals(
                    FileTranslationsApi::ENDPOINT_URL . '/' . $this->accountUid . '/files/' . $fileUid . '/mt/' . $mtUid . '/locales/all/file/zip',
                    $uri
                );

                // Verify Accept header is removed for raw download
                $this->assertArrayNotHasKey('Accept', $options['headers']);

                return $responseMock;
            });

        $result = $this->object->downloadAllTranslationsZip($fileUid, $mtUid);
        $this->assertEquals($rawContent, $result);
    }

    /**
     * @covers \Smartling\FileTranslations\FileTranslationsApi::cancelFileTranslation
     */
    public function testCancelFileTranslation()
    {
        $this->prepareClientResponseMock();

        $fileUid = 'file-uid-123';
        $mtUid = 'mt-uid-456';

        $this->client
            ->expects($this->once())
            ->method('request')
            ->willReturnCallback(function(string $method, string $uri, array $options) use ($fileUid, $mtUid) {
                $this->assertEquals('post', $method);
                $this->assertEquals(
                    FileTranslationsApi::ENDPOINT_URL . '/' . $this->accountUid . '/files/' . $fileUid . '/mt/' . $mtUid . '/cancel',
                    $uri
                );

                // Verify it's a JSON request
                $this->assertArrayHasKey('json', $options);

                return $this->responseMock;
            });

        $result = $this->object->cancelFileTranslation($fileUid, $mtUid);
        // Result can be either true (empty data) or an array (with data)
        $this->assertTrue($result === true || is_array($result));
    }
}
