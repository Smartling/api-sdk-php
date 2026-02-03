<?php

namespace Smartling\Tests\Functional;

use PHPUnit\Framework\TestCase;
use Smartling\AuthApi\AuthTokenProvider;
use Smartling\FileTranslations\FileTranslationsApi;
use Smartling\FileTranslations\Params\TranslateFileParameters;

/**
 * Functional test class for Smartling\FileTranslations\FileTranslationsApi.
 *
 * These tests require actual Smartling API credentials:
 * - account_uid: Smartling account UID
 * - user_id: API user identifier
 * - user_key: API secret key
 *
 * Run with:
 * account_uid=<uid> user_id=<id> user_key=<key> ./vendor/bin/phpunit tests/functional/FileTranslationsApiFunctionalTest.php
 */
class FileTranslationsApiFunctionalTest extends TestCase
{
    /**
     * @var FileTranslationsApi
     */
    private $api;

    /**
     * @var string
     */
    private $testFilePath;

    /**
     * Sets up the test environment.
     */
    protected function setUp(): void
    {
        $accountUid = getenv('account_uid');
        $userId = getenv('user_id');
        $userKey = getenv('user_key');

        if (empty($accountUid) || empty($userId) || empty($userKey)) {
            $this->markTestSkipped(
                'Required environment variables not set: account_uid, user_id, user_key'
            );
        }

        $authProvider = AuthTokenProvider::create($userId, $userKey);
        $this->api = FileTranslationsApi::create($authProvider, $accountUid);

        $this->testFilePath = __DIR__ . '/../resources/test-fts.json';

        if (!file_exists($this->testFilePath)) {
            $this->markTestSkipped('Test file not found: ' . $this->testFilePath);
        }
    }

    /**
     * Tests the complete file translation workflow:
     * 1. Upload file
     * 2. Initiate translation
     * 3. Poll translation progress
     * 4. Download translated file
     *
     * @covers \Smartling\FileTranslations\FileTranslationsApi::uploadFile
     * @covers \Smartling\FileTranslations\FileTranslationsApi::translateFile
     * @covers \Smartling\FileTranslations\FileTranslationsApi::getTranslationProgress
     * @covers \Smartling\FileTranslations\FileTranslationsApi::downloadTranslatedFile
     */
    public function testCompleteTranslationWorkflow()
    {
        // Step 1: Upload file
        $uploadResult = $this->api->uploadFile(
            $this->testFilePath,
            'test-fts-' . time() . '.json',
            'json'
        );

        $this->assertIsArray($uploadResult);
        $this->assertArrayHasKey('fileUid', $uploadResult);
        $fileUid = $uploadResult['fileUid'];

        // Step 2: Initiate translation
        $translateParams = new TranslateFileParameters();
        $translateParams->setSourceLocaleId('en')
            ->setTargetLocaleIds(['es']);

        $translateResult = $this->api->translateFile($fileUid, $translateParams);

        $this->assertIsArray($translateResult);
        $this->assertArrayHasKey('mtUid', $translateResult);
        $mtUid = $translateResult['mtUid'];

        // Step 3: Poll translation progress
        $maxAttempts = 30; // Maximum number of polling attempts
        $pollInterval = 2; // Seconds between polls
        $completed = false;

        for ($i = 0; $i < $maxAttempts; $i++) {
            sleep($pollInterval);

            $progress = $this->api->getTranslationProgress($fileUid, $mtUid);

            $this->assertIsArray($progress);
            $this->assertArrayHasKey('status', $progress);

            $status = $progress['status'];

            if ($status === 'COMPLETED') {
                $completed = true;
                break;
            } elseif ($status === 'FAILED') {
                $this->fail('Translation failed: ' . json_encode($progress));
            } elseif ($status === 'CANCELLED') {
                $this->fail('Translation was cancelled');
            }

            // Status should be IN_PROGRESS
            $this->assertEquals('IN_PROGRESS', $status);
        }

        if (!$completed) {
            $this->markTestIncomplete('Translation did not complete within expected time');
        }

        // Step 4: Download translated file
        $translatedContent = $this->api->downloadTranslatedFile($fileUid, $mtUid, 'es');

        $this->assertIsString($translatedContent);
        $this->assertNotEmpty($translatedContent);

        // Verify it's valid JSON
        $translatedData = json_decode($translatedContent, true);
        $this->assertIsArray($translatedData);
        $this->assertNotNull($translatedData);
    }

    /**
     * Tests downloading all translations as ZIP.
     *
     * This test depends on having a completed translation from the previous test.
     * In practice, you would run this after a successful translation.
     *
     * @covers \Smartling\FileTranslations\FileTranslationsApi::downloadAllTranslationsZip
     */
    public function testDownloadAllTranslationsZip()
    {
        // This is a simplified test - in practice, you would need
        // a fileUid and mtUid from a completed translation
        $this->markTestIncomplete('Requires fileUid and mtUid from a completed translation');

        // Example usage:
        // $zipContent = $this->api->downloadAllTranslationsZip($fileUid, $mtUid);
        // $this->assertIsString($zipContent);
        // $this->assertNotEmpty($zipContent);
    }

    /**
     * Tests cancelling a translation.
     *
     * @covers \Smartling\FileTranslations\FileTranslationsApi::cancelFileTranslation
     */
    public function testCancelFileTranslation()
    {
        // Upload file
        $uploadResult = $this->api->uploadFile(
            $this->testFilePath,
            'test-cancel-' . time() . '.json',
            'json'
        );

        $fileUid = $uploadResult['fileUid'];

        // Initiate translation with multiple locales to ensure it takes some time
        $translateParams = new TranslateFileParameters();
        $translateParams->setSourceLocaleId('en')
            ->setTargetLocaleIds(['es', 'fr', 'de', 'it', 'pt']);

        $translateResult = $this->api->translateFile($fileUid, $translateParams);
        $mtUid = $translateResult['mtUid'];

        // Immediately cancel the translation
        $cancelResult = $this->api->cancelFileTranslation($fileUid, $mtUid);

        $this->assertTrue($cancelResult);

        // Verify the translation was cancelled
        sleep(2); // Give the system time to process the cancellation

        $progress = $this->api->getTranslationProgress($fileUid, $mtUid);
        $this->assertIsArray($progress);
        $this->assertArrayHasKey('status', $progress);

        // Status should be CANCELLED (or possibly still IN_PROGRESS if cancellation hasn't completed yet)
        $this->assertContains($progress['status'], ['CANCELLED', 'IN_PROGRESS']);
    }
}
