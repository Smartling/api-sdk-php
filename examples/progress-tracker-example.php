<?php

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
    'account-uid:'
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
}
else {
    /** @noinspection UntrustedInclusionInspection */
    require_once '../vendor/autoload.php';
}

$projectId = $options['project-id'];
$accountUid = $options['account-uid'];
$userIdentifier = $options['user-id'];
$userSecretKey = $options['secret-key'];
$authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param $accountUid
 * @return bool
 */
function getTokenDemo($authProvider, $projectId, $accountUid)
{
    $response = false;
    $progressTracker = \Smartling\ProgressTracker\ProgressTrackerApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $response = $progressTracker->getToken($accountUid);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(true);
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
 * @param $spaceId
 * @param $objectId
 * @return bool
 */
function createRecordDemo($authProvider, $projectId, $spaceId, $objectId)
{
    $response = false;
    $progressTracker = \Smartling\ProgressTracker\ProgressTrackerApi::create($authProvider, $projectId);
    $st = microtime(true);

    $params = new \Smartling\ProgressTracker\Params\RecordParameters();
    $params->setTtl(15);
    $params->setData([
      "foo" => "bar"
    ]);

    try {
        $response = $progressTracker->createRecord($spaceId, $objectId, $params);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(true);
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
 * @param $spaceId
 * @param $objectId
 * @param $recordId
 * @return bool
 */
function deleteRecordDemo($authProvider, $projectId, $spaceId, $objectId, $recordId)
{
  $response = false;
  $progressTracker = \Smartling\ProgressTracker\ProgressTrackerApi::create($authProvider, $projectId);
  $st = microtime(true);

  try {
    $response = $progressTracker->deleteRecord($spaceId, $objectId, $recordId);
  } catch (\Smartling\Exceptions\SmartlingApiException $e) {
    var_dump($e->getErrors());
  }

  $et = microtime(true);
  $time = $et - $st;

  echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

  if (!empty($response)) {
    var_dump($response);
  }

  return $response;
}

$response = getTokenDemo($authProvider, $projectId, $accountUid);
$response = createRecordDemo($authProvider, $projectId, "space", "object");
$response = deleteRecordDemo($authProvider, $projectId, "space", "object", $response["recordId"]);
