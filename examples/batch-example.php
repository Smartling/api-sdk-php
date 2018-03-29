<?php

/**
 * This file contains examples of Smartling API 2.x usage.
 *
 * How to use:
 * run "php example.php --project-id={PROJECT_ID} --user-id={USER_IDENTIFIER} --secret-key={SECRET_KEY}" in console
 *
 * Be sure you that dependencies are solved bu composer BEFORE running.
 */

use Smartling\Batch\BatchApi;
use Smartling\Batch\Params\CreateBatchParameters;
use Smartling\File\Params\UploadFileParameters;

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

$autoloader = '../vendor/autoload.php';

if (!file_exists($autoloader) || !is_readable($autoloader)) {
    echo 'Error. Autoloader not found. Seems you didn\'t run:' . PHP_EOL . '    composer update' . PHP_EOL;
    exit;
} else {
    /** @noinspection UntrustedInclusionInspection */
    require_once $autoloader;
}

$projectId = $options['project-id'];
$userIdentifier = $options['user-id'];
$userSecretKey = $options['secret-key'];
$authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 *
 * @return bool
 */
function createBatchDemo($authProvider, $projectId, $jobId)
{
    echo "--- Create Batch ---\n";

    $response = [];
    $batchApi = BatchApi::create($authProvider, $projectId);
    $createBatchParameters = new CreateBatchParameters();
    $createBatchParameters->setTranslationJobUid($jobId);
    $createBatchParameters->setAuthorize(true);
    $st = microtime(true);

    try {
        $response = $batchApi->createBatch($createBatchParameters);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(TRUE);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

    if (!empty($response)) {
        var_dump($response);
    }

    return $response;
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param int $batchUid
 *
 * @return bool
 */
function uploadBatchFileDemo($authProvider, $projectId, $batchUid)
{
    echo "--- Upload file ---\n";

    $response = [];
    $batchApi = BatchApi::create($authProvider, $projectId);
    $uploadParameters = new UploadFileParameters();
    $uploadParameters->setLocalesToApprove(['fr-FR']);
    $st = microtime(true);

    try {
        $response = $batchApi->uploadBatchFile(realpath('../tests/resources/test.xml'), 'test-BATCH-file.xml', 'xml', $batchUid, $uploadParameters);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(TRUE);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

    if (!empty($response)) {
        var_dump($response);
    }

    return $response;
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param int $batchUid
 *
 * @return bool
 */
function executeBatchDemo($authProvider, $projectId, $batchUid)
{
    echo "--- Execute Batch ---\n";

    $response = [];
    $batchApi = BatchApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $response = $batchApi->executeBatch($batchUid);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(TRUE);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

    if (!empty($response)) {
        var_dump($response);
    }

    return $response;
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param int $batchUid
 *
 * @return bool
 */
function getBatchStatusDemo($authProvider, $projectId, $batchUid)
{
    echo "--- Get Batch status ---\n";

    $response = [];
    $batchApi = BatchApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $response = $batchApi->getBatchStatus($batchUid);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(TRUE);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

    if (!empty($response)) {
        var_dump($response);
    }

    return $response;
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 *
 * @return bool
 */
function listBatchesDemo($authProvider, $projectId)
{
  echo "--- List Batches ---\n";

  $response = [];
  $batchApi = BatchApi::create($authProvider, $projectId);
  $st = microtime(true);

  try {
    $response = $batchApi->listBatches();
  } catch (\Smartling\Exceptions\SmartlingApiException $e) {
    var_dump($e->getErrors());
  }

  $et = microtime(TRUE);
  $time = $et - $st;

  echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

  if (!empty($response)) {
    var_dump($response);
  }

  return $response;
}

$response = createBatchDemo($authProvider, $projectId, '[job_id]');
$batchUid = $response['batchUid'];
$response = uploadBatchFileDemo($authProvider, $projectId, $batchUid);
$response = executeBatchDemo($authProvider, $projectId, $batchUid);
$response = getBatchStatusDemo($authProvider, $projectId, $batchUid);
$response = listBatchesDemo($authProvider, $projectId);
