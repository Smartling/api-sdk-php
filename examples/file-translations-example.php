<?php

/**
 * File Translations API Example
 *
 * This example demonstrates how to use the File Translations API to:
 * 1. Upload a file for machine translation
 * 2. Initiate translation to multiple target locales
 * 3. Poll translation progress
 * 4. Download translated files
 * 5. Download all translations as ZIP
 * 6. Cancel a translation (optional)
 *
 * Usage:
 *   php file-translations-example.php --account-uid=XXX --user-id=XXX --secret-key=XXX [--file-path=path/to/file.json]
 */

require_once '../vendor/autoload.php';

use Smartling\AuthApi\AuthTokenProvider;
use Smartling\FileTranslations\FileTranslationsApi;
use Smartling\FileTranslations\Params\TranslateFileParameters;
use Smartling\FileTranslations\Params\UploadFileParameters;

// Parse command line arguments
$options = getopt('', [
    'account-uid:',
    'user-id:',
    'secret-key:',
    'file-path::',
]);

if (!isset($options['account-uid']) || !isset($options['user-id']) || !isset($options['secret-key'])) {
    echo "Usage: php file-translations-example.php --account-uid=XXX --user-id=XXX --secret-key=XXX [--file-path=path/to/file.json]\n";
    exit(1);
}

$accountUid = $options['account-uid'];
$userId = $options['user-id'];
$secretKey = $options['secret-key'];
$filePath = $options['file-path'] ?? __DIR__ . '/../tests/resources/test-fts.json';

// Verify file exists
if (!file_exists($filePath)) {
    echo "Error: File not found: {$filePath}\n";
    exit(1);
}

try {
    echo "=== File Translations API Example ===\n\n";

    // Step 1: Initialize API client
    echo "1. Initializing API client...\n";
    $authProvider = AuthTokenProvider::create($userId, $secretKey);
    $api = FileTranslationsApi::create($authProvider, $accountUid);
    echo "   ✓ API client initialized\n\n";

    // Step 2: Upload file
    echo "2. Uploading file: {$filePath}\n";
    $uploadParams = new UploadFileParameters();
    $fileName = basename($filePath);
    $fileType = pathinfo($filePath, PATHINFO_EXTENSION);

    $uploadResult = $api->uploadFile($filePath, $fileName, $fileType, $uploadParams);
    $fileUid = $uploadResult['fileUid'];
    echo "   ✓ File uploaded successfully\n";
    echo "   File UID: {$fileUid}\n\n";

    // Step 3: Initiate translation
    echo "3. Initiating translation to Spanish, French, and German...\n";
    $translateParams = new TranslateFileParameters();
    $translateParams
        ->setSourceLocaleId('en')
        ->setTargetLocaleIds(['es', 'fr', 'de']);

    $translateResult = $api->translateFile($fileUid, $translateParams);
    $mtUid = $translateResult['mtUid'];
    echo "   ✓ Translation initiated\n";
    echo "   MT UID: {$mtUid}\n\n";

    // Step 4: Poll translation progress
    echo "4. Polling translation progress...\n";
    $maxAttempts = 60;
    $pollInterval = 5; // seconds
    $completed = false;

    for ($i = 0; $i < $maxAttempts; $i++) {
        $progress = $api->getTranslationProgress($fileUid, $mtUid);
        $status = $progress['status'];

        echo "   Status: {$status}";

        if (isset($progress['completedLocales'])) {
            $completedCount = count($progress['completedLocales']);
            $totalCount = count($translateParams->exportToArray()['targetLocaleIds']);
            echo " ({$completedCount}/{$totalCount} locales completed)";
        }

        echo "\n";

        if ($status === 'COMPLETED') {
            $completed = true;
            echo "   ✓ Translation completed!\n\n";
            break;
        } elseif ($status === 'FAILED') {
            echo "   ✗ Translation failed\n";
            print_r($progress);
            exit(1);
        } elseif ($status === 'CANCELLED') {
            echo "   ✗ Translation was cancelled\n";
            exit(1);
        }

        if ($i < $maxAttempts - 1) {
            sleep($pollInterval);
        }
    }

    if (!$completed) {
        echo "   ⚠ Translation not completed within expected time. Continuing anyway...\n\n";
    }

    // Step 5: Download translated files
    echo "5. Downloading translated files...\n";
    $targetLocales = ['es', 'fr', 'de'];

    foreach ($targetLocales as $locale) {
        try {
            $translatedContent = $api->downloadTranslatedFile($fileUid, $mtUid, $locale);
            $outputPath = "/tmp/translated-{$locale}-{$fileName}";
            file_put_contents($outputPath, $translatedContent);
            echo "   ✓ Downloaded {$locale}: {$outputPath}\n";

            // Show first 100 chars of content
            $preview = substr($translatedContent, 0, 100);
            if (strlen($translatedContent) > 100) {
                $preview .= '...';
            }
            echo "     Preview: {$preview}\n";
        } catch (Exception $e) {
            echo "   ✗ Failed to download {$locale}: {$e->getMessage()}\n";
        }
    }
    echo "\n";

    // Step 6: Download all translations as ZIP
    echo "6. Downloading all translations as ZIP...\n";
    try {
        $zipContent = $api->downloadAllTranslationsZip($fileUid, $mtUid);
        $zipPath = "/tmp/all-translations-{$fileUid}.zip";
        file_put_contents($zipPath, $zipContent);
        echo "   ✓ Downloaded ZIP: {$zipPath}\n";
        echo "     Size: " . strlen($zipContent) . " bytes\n\n";
    } catch (Exception $e) {
        echo "   ✗ Failed to download ZIP: {$e->getMessage()}\n\n";
    }

    // Optional: Demonstrate cancellation with a new translation
    echo "7. (Optional) Demonstrating translation cancellation...\n";
    echo "   Uploading another file...\n";
    $uploadResult2 = $api->uploadFile($filePath, "cancel-demo-{$fileName}", $fileType, $uploadParams);
    $fileUid2 = $uploadResult2['fileUid'];

    echo "   Starting translation to many locales...\n";
    $translateParams2 = new TranslateFileParameters();
    $translateParams2
        ->setSourceLocaleId('en')
        ->setTargetLocaleIds(['es', 'fr', 'de', 'it', 'pt', 'ja', 'zh', 'ru']);

    $translateResult2 = $api->translateFile($fileUid2, $translateParams2);
    $mtUid2 = $translateResult2['mtUid'];

    echo "   Cancelling translation...\n";
    sleep(1); // Give it a moment to start
    $api->cancelFileTranslation($fileUid2, $mtUid2);
    echo "   ✓ Cancellation request sent\n";

    sleep(2);
    $progress = $api->getTranslationProgress($fileUid2, $mtUid2);
    echo "   Final status: {$progress['status']}\n\n";

    echo "=== Example completed successfully ===\n";

} catch (Exception $e) {
    echo "\nError: {$e->getMessage()}\n";
    if (method_exists($e, 'getTraceAsString')) {
        echo $e->getTraceAsString() . "\n";
    }
    exit(1);
}
