<?php

namespace Smartling\Tests\Functional;

use GuzzleHttp\Psr7\Stream;
use PHPUnit_Framework_TestCase;
use Smartling\AuthApi\AuthTokenProvider;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\File\FileApi;
use Smartling\File\Params\DownloadFileParameters;
use Smartling\File\Params\ListFilesParameters;

/**
 * Test class for File API examples.
 */
class FileApiFunctionalTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var FileApi
     */
    private $fileApi;

    /**
     * @var string
     */
    const FILE_NAME = 'test.xml';

    /**
     * @var string
     */
    const NEW_FILE_NAME = 'new_test.xml';

    /**
     * @var string
     */
    private $fileUri;

    /**
     * @var string
     */
    private $fileRealPath;

    /**
     * @var string
     */
    private $fileType;

    /**
     * @var string
     */
    private $retrievalType;

    /**
     * @var string
     */
    private $translationState;

    /**
     * @var string
     */
    private $targetLocale;

    /**
     * Reset all files in Smartling after tests.
     */
    public static function tearDownAfterClass() {
        $authProvider = AuthTokenProvider::create(getenv('user_id'), getenv('user_key'));
        $fileApi = FileApi::create($authProvider, getenv('project_id'));

        foreach ([self::FILE_NAME, self::NEW_FILE_NAME] as $file) {
            try {
                $fileApi->deleteFile($file);
            }
            catch (SmartlingApiException $e) {

            }
        }
    }

    /**
     * Test mixture.
     */
    public function setUp() {
        $projectId = getenv('project_id');
        $userIdentifier = getenv('user_id');
        $userSecretKey = getenv('user_key');

        if (
            empty($projectId) ||
            empty($userIdentifier) ||
            empty($userSecretKey)
        ) {
            $this->fail('Missing required parameters');
        }

        $this->fileUri = 'tests/resources/test.xml';
        $this->fileRealPath = realpath($this->fileUri);
        $this->fileType = 'xml';
        $this->retrievalType = 'pseudo';
        $this->translationState = 'PUBLISHED';
        $this->targetLocale = 'ru-RU';

        $authProvider = AuthTokenProvider::create($userIdentifier, $userSecretKey);
        $this->fileApi = FileApi::create($authProvider, $projectId);
      }

    /**
     * Test for file upload.
     */
    public function testFileApiUploadFile() {
        try {
            $result = $this->fileApi->uploadFile($this->fileRealPath, self::FILE_NAME, $this->fileType);

            $this->assertArrayHasKey('wordCount', $result);
            $this->assertArrayHasKey('stringCount', $result);
            $this->assertArrayHasKey('overWritten', $result);
        }
        catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for retrieving file last modified date.
     */
    public function testFileApiLastModified() {
        try {
            $result = $this->fileApi->lastModified(self::FILE_NAME);

            $this->assertArrayHasKey('totalCount', $result);
            $this->assertArrayHasKey('items', $result);

            date_default_timezone_set('Pacific/Auckland');
            $result_new_timezone = $this->fileApi->lastModified(self::FILE_NAME);

            $this->assertEquals(
                $result['items'][0]['lastModified']->getTimestamp(),
                $result_new_timezone['items'][0]['lastModified']->getTimestamp()
            );
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for file download.
     */
    public function testFileApiDownloadFile() {
        try {
            $params = new DownloadFileParameters();
            $params->setRetrievalType($this->retrievalType);
            $result = $this->fileApi->downloadFile(self::FILE_NAME, $this->targetLocale, $params);

            $this->assertInstanceOf(Stream::class, $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for retrieving file status.
     */
    public function testFileApiGetStatus() {
        try {
            $result = $this->fileApi->getStatus(self::FILE_NAME, $this->targetLocale);

            $this->assertArrayHasKey('fileUri', $result);
            $this->assertArrayHasKey('lastUploaded', $result);
            $this->assertArrayHasKey('created', $result);
            $this->assertArrayHasKey('fileType', $result);
            $this->assertArrayHasKey('parserVersion', $result);
            $this->assertArrayHasKey('hasInstructions', $result);
            $this->assertArrayHasKey('totalStringCount', $result);
            $this->assertArrayHasKey('totalWordCount', $result);
            $this->assertArrayHasKey('authorizedStringCount', $result);
            $this->assertArrayHasKey('authorizedWordCount', $result);
            $this->assertArrayHasKey('completedStringCount', $result);
            $this->assertArrayHasKey('completedWordCount', $result);
            $this->assertArrayHasKey('excludedStringCount', $result);
            $this->assertArrayHasKey('excludedWordCount', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for retrieving file status for all locales.
     */
    public function testFileApiGetStatusForAllLocales() {
        try {
            $result = $this->fileApi->getStatusForAllLocales(self::FILE_NAME);

            $this->assertArrayHasKey('fileUri', $result);
            $this->assertArrayHasKey('lastUploaded', $result);
            $this->assertArrayHasKey('created', $result);
            $this->assertArrayHasKey('fileType', $result);
            $this->assertArrayHasKey('parserVersion', $result);
            $this->assertArrayHasKey('hasInstructions', $result);
            $this->assertArrayHasKey('totalStringCount', $result);
            $this->assertArrayHasKey('totalWordCount', $result);
            $this->assertArrayHasKey('totalCount', $result);
            $this->assertArrayHasKey('items', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for retrieving authorized locales.
     */
    public function testFileApiGetAuthorizedLocales() {
        try {
            $result = $this->fileApi->getAuthorizedLocales(self::FILE_NAME);

            $this->assertArrayHasKey('items', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for retrieving file list.
     */
    public function testFileApiGetList() {
        try {
            $params = new ListFilesParameters();
            $params->setFileTypes($this->fileType)
                ->setUriMask('test')
                ->setLimit(5);

            $result = $this->fileApi->getList($params);

            $this->assertArrayHasKey('items', $result);
            $this->assertArrayHasKey('totalCount', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for file import.
     */
    public function testFileApiImport() {
        try {
            $result = $this->fileApi->import($this->targetLocale, self::FILE_NAME, $this->fileType, $this->fileRealPath, $this->translationState, TRUE);

            $this->assertArrayHasKey('wordCount', $result);
            $this->assertArrayHasKey('stringCount', $result);
            $this->assertArrayHasKey('translationImportErrors', $result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for renaming file.
     */
    public function testFileApiRenameFile() {
        try {
            $result = $this->fileApi->renameFile(self::FILE_NAME, self::NEW_FILE_NAME);

            $this->assertTrue($result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Test for deleting.
     */
    public function testFileApiDeleteFile() {
        try {
            $result = $this->fileApi->deleteFile(self::NEW_FILE_NAME);

            $this->assertTrue($result);
        } catch (SmartlingApiException $e) {
            $this->fail($e->getMessage());
        }
    }

}
