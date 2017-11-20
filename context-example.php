<?php

/**
 * This file contains examples of Smartling API 2.x usage.
 *
 * How to use:
 * run "php example.php --project-id={PROJECT_ID} --user-id={USER_IDENTIFIER} --secret-key={SECRET_KEY}" in console
 *
 * Be sure you that dependencies are solved bu composer BEFORE running.
 */

use Smartling\Context\Params\UploadContextParameters;
use Smartling\Context\Params\UploadResourceParameters;

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
$authProvider = \Smartling\AuthApi\AuthTokenProvider::create($userIdentifier, $userSecretKey);

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param string $fileUri
 * @return array
 */
function uploadContextDemo($authProvider, $projectId, $fileUri)
{
    $response = FALSE;
    $context = \Smartling\Context\ContextApi::create($authProvider, $projectId);
    $params = new UploadContextParameters();
    $params->setContextFileUri($fileUri);
    $params->setName('context.html');
    $st = microtime(true);

    try {
        $response = $context->uploadContext($params);
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
 * @param $contextUid
 * @return array
 */
function matchContextDemo($authProvider, $projectId, $contextUid)
{
    $response = FALSE;
    $context = \Smartling\Context\ContextApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $response = $context->matchContext($contextUid);
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
 * @param string $fileUri
 * @return array
 */
function uploadAndMatchContextDemo($authProvider, $projectId, $fileUri)
{
    $response = FALSE;
    $context = \Smartling\Context\ContextApi::create($authProvider, $projectId);
    $params = new UploadContextParameters();
    $params->setContextFileUri($fileUri);
    $params->setName('context.html');
    $st = microtime(true);

    try {
        $response = $context->uploadAndMatchContext($params);
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
 * @return array
 */
function getMissingResources($authProvider, $projectId)
{
    $response = FALSE;
    $context = \Smartling\Context\ContextApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $response = $context->getMissingResources();
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
 * @return bool
 */
function getAllMissingResourcesDemo($authProvider, $projectId) {
    $response = FALSE;
    $context = \Smartling\Context\ContextApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $response = $context->getAllMissingResources();
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
 * @param string $resourceId
 * @param string $fileUri
 * @return bool
 */
function uploadResourceDemo($authProvider, $projectId, $resourceId, $fileUri) {
    $response = FALSE;
    $context = \Smartling\Context\ContextApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $params = new UploadResourceParameters();
        $params->setFile($fileUri);
        $response = $context->uploadResource($resourceId, $params);
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
 * @param $contextUid
 * @return array
 */
function renderContextDemo($authProvider, $projectId, $contextUid)
{
    $response = FALSE;
    $context = \Smartling\Context\ContextApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $response = $context->renderContext($contextUid);
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

$contextInfo = uploadContextDemo($authProvider, $projectId, 'tests/resources/context.html');
$response = matchContextDemo($authProvider, $projectId, $contextInfo['contextUid']);
$response = uploadAndMatchContextDemo($authProvider, $projectId, 'tests/resources/context.html');
$missingResources = getMissingResources($authProvider, $projectId);
$allMissingResources = getAllMissingResourcesDemo($authProvider, $projectId);
$uploadedResourceResponse = uploadResourceDemo($authProvider, $projectId, '[resource_id]', 'tests/resources/test.png');
$response = renderContextDemo($authProvider, $projectId, $contextInfo['contextUid']);
