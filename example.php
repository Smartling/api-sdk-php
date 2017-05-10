<?php

use Smartling\Jobs\Params\SearchJobsParameters;

error_reporting(E_ALL);
/**
 * This file contains examples of Smartling API 2.x usage.
 *
 * How to use:
 * run "php example.php --project-id={PROJECT_ID} --user-id={USER_IDENTIFIER} --secret-key={SECRET_KEY}" in console
 *
 * Be sure you that dependencies are solved bu composer BEFORE running.
 */

$longOpts = [
    'project-id:',
    'user-id:',
    'secret-key:',
];

$options = getopt('', $longOpts);

if (
    !array_key_exists('project-id', $options)
    || !array_key_exists('user-id', $options)
    || !array_key_exists('secret-key', $options)
) {
    echo 'Missing required params.' . PHP_EOL;
    exit;
}

$autoloader = 'vendor/autoload.php';

if (!file_exists($autoloader) || !is_readable($autoloader)) {
    echo 'Error. Autoloader not found. Seems you didn\'t run:' . PHP_EOL . '    composer update' . PHP_EOL;
    exit;
} else {
    /** @noinspection UntrustedInclusionInspection */
    require_once 'vendor/autoload.php';
}

$projectId = $options['project-id'];
$userIdentifier = $options['user-id'];
$userSecretKey = $options['secret-key'];


$fileName = 'test.xml';
$fileUri = 'tests/resources/test.xml';
$fileRealPath = realpath($fileUri);
$fileType = 'xml';
$newFileName = 'new_test_file.xml';
$retrievalType = 'pseudo';
$content = file_get_contents(realpath($fileUri));
$fileContentUri = 'testing_content.xml';
$translationState = 'PUBLISHED';
$locale = 'ru-RU';
$locales_array = [$locale];


resetFiles($userIdentifier, $userSecretKey, $projectId, [$fileName, $newFileName]);


/**
 * Upload file example
 */

try {
    echo '::: File Upload Example :::' . PHP_EOL;

    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

    $result = $fileApi->uploadFile($fileRealPath, $fileName, $fileType);

    echo 'File upload result:' . PHP_EOL;
    echo var_export($result, true) . PHP_EOL . PHP_EOL;

} catch (\Smartling\Exceptions\SmartlingApiException $e) {
    echo $e->formatErrors('Error happened while uploading file');
}

/**
 * Last Modified file example
 */
try {
    echo '::: Last Modified Example :::' . PHP_EOL;

    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

    $result = $fileApi->lastModified($fileName);

    echo 'Last Modified result:' . PHP_EOL;
    echo var_export($result, true) . PHP_EOL . PHP_EOL;

} catch (\Smartling\Exceptions\SmartlingApiException $e) {
    echo $e->formatErrors('Error happened while getting last modified');
}

/**
 * Download file example
 */
try {
    echo '::: File Download Example :::' . PHP_EOL;

    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

    $params = new \Smartling\File\Params\DownloadFileParameters();
    $params->setRetrievalType($retrievalType);

    $result = $fileApi->downloadFile($fileName, $locale, $params);

    echo 'File download result:' . PHP_EOL;
    echo var_export($result, true) . PHP_EOL . PHP_EOL;

} catch (\Smartling\Exceptions\SmartlingApiException $e) {
    echo $e->formatErrors('Error happened while downloading file');
}

/**
 * Getting file status example
 */
try {
    echo '::: Get File Status Example :::' . PHP_EOL;

    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

    $result = $fileApi->getStatus($fileName, $locale);

    echo 'Get File Status result:' . PHP_EOL;
    echo var_export($result, true) . PHP_EOL . PHP_EOL;

} catch (\Smartling\Exceptions\SmartlingApiException $e) {
    echo $e->formatErrors('Error happened while getting file status');
}


/**
 * Getting file status for all locales example example
 */
try {
    echo '::: Get File Status For All Locales Example :::' . PHP_EOL;

    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

    $result = $fileApi->getStatusForAllLocales($fileName);

    echo 'Get File Status For All Locales result:' . PHP_EOL;
    echo var_export($result, true) . PHP_EOL . PHP_EOL;

} catch (\Smartling\Exceptions\SmartlingApiException $e) {
    echo $e->formatErrors('Error happened while getting file status for all locales');
}



/**
 * Getting Authorized locales for file
 */
try {
    echo '::: Get File Authorized Locales Example :::' . PHP_EOL;

    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

    $result = $fileApi->getAuthorizedLocales($fileName);

    echo 'Get File Authorized Locales result:' . PHP_EOL;
    echo var_export($result, true) . PHP_EOL . PHP_EOL;

} catch (\Smartling\Exceptions\SmartlingApiException $e) {
    echo $e->formatErrors('Error happened while getting file authorized locales');
}

/**
 * Listing Files
 */
try {
    echo '::: List Files Example :::' . PHP_EOL;

    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

    $params = new \Smartling\File\Params\ListFilesParameters();
    $params
        ->setFileTypes($fileType)
        ->setUriMask('test')
        ->setLimit(5);

    $result = $fileApi->getList($params);

    echo 'List Files result:' . PHP_EOL;
    echo var_export($result, true) . PHP_EOL . PHP_EOL;

} catch (\Smartling\Exceptions\SmartlingApiException $e) {
    echo $e->formatErrors('Error happened while getting file list');
}

/**
 * Importing Files
 */
try {
    echo '::: File Import Example :::' . PHP_EOL;

    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

    $result = $fileApi->import($locale, $fileName, $fileType, $fileRealPath, $translationState, true);

    echo 'File Import result:' . PHP_EOL;
    echo var_export($result, true) . PHP_EOL . PHP_EOL;

} catch (\Smartling\Exceptions\SmartlingApiException $e) {
    echo $e->formatErrors('Error happened while importing file');
}

/**
 * Renaming Files
 */
try {
    echo '::: Rename File Example :::' . PHP_EOL;

    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

    $result = $fileApi->renameFile($fileName, $newFileName);

    echo 'Rename File result:' . PHP_EOL;
    echo var_export($result, true) . PHP_EOL . PHP_EOL;

} catch (\Smartling\Exceptions\SmartlingApiException $e) {
    echo $e->formatErrors('Error happened while renaming files');
}

/**
 * Deleting Files
 */
try {
    echo '::: File Deletion Example :::' . PHP_EOL;

    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

    $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);

    $result = $fileApi->deleteFile($newFileName);

    echo 'File Delete result:' . PHP_EOL;
    echo var_export($result, true) . PHP_EOL . PHP_EOL;

} catch (\Smartling\Exceptions\SmartlingApiException $e) {
    echo $e->formatErrors('Error happened while deleting file');
}

/**
 * Search Job example.
 */

try {
    echo '::: Job Search Example :::' . PHP_EOL;

    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

    $jobsApi = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);

    $searchParameters = new SearchJobsParameters();
    $searchParameters->setFileUris([
        'some_file_to_search.xml',
    ]);
    $result = $jobsApi->searchJobs($searchParameters);

    echo 'Job search result:' . PHP_EOL;
    echo var_export($result, true) . PHP_EOL . PHP_EOL;

} catch (\Smartling\Exceptions\SmartlingApiException $e) {
    $messageTemplate = 'Error happened while searching job.' . PHP_EOL
        . 'Response code: %s' . PHP_EOL
        . 'Response message: %s' . PHP_EOL;

    echo vsprintf(
        $messageTemplate,
        [
            $e->getCode(),
            $e->getMessage(),
        ]
    );
}


/** @noinspection MoreThanThreeArgumentsInspection
 *
 * @param string $userIdentifier
 * @param string $userSecretKey
 * @param string $projectId
 * @param array $files
 */
function resetFiles($userIdentifier, $userSecretKey, $projectId, array $files = [])
{
    echo '::: Reset File Example :::' . PHP_EOL;
    
    $authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);
    foreach ($files as $file) {
        try {
            $fileApi = \Smartling\File\FileApi::create($authProvider, $projectId);
            $fileApi->deleteFile($file);
        } catch (\Smartling\Exceptions\SmartlingApiException $e) {
            echo $e->formatErrors();
        }
    }
}