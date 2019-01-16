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
 * @return bool
 */
function searchProjectLevelLogRecordDemo($authProvider, $projectId)
{
    $response = false;
    $auditLog = \Smartling\AuditLog\AuditLogApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $searchParams = (new \Smartling\AuditLog\Params\SearchRecordParameters())
            ->setSearchQuery('clientData.foo:bar')
            ->setOffset(0)
            ->setLimit(100)
            ->setSort('actionTime', \Smartling\AuditLog\Params\SearchRecordParameters::ORDER_DESC);

        $response = $auditLog->searchProjectLevelLogRecord($searchParams);
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
 * @param string $accountUid
 * @return bool
 */
function searchAccountLevelLogRecordDemo($authProvider, $projectId, $accountUid)
{
    $response = false;
    $auditLog = \Smartling\AuditLog\AuditLogApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $searchParams = (new \Smartling\AuditLog\Params\SearchRecordParameters())
            ->setSearchQuery('clientData.foo:bar')
            ->setOffset(0)
            ->setLimit(100)
            ->setSort('actionTime', \Smartling\AuditLog\Params\SearchRecordParameters::ORDER_DESC);

        $response = $auditLog->searchAccountLevelLogRecord($accountUid, $searchParams);
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
 * @return bool
 */
function createProjectLevelLogRecordDemo($authProvider, $projectId)
{
    $response = false;
    $auditLog = \Smartling\AuditLog\AuditLogApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $createParams = (new \Smartling\AuditLog\Params\CreateRecordParameters())
            ->setActionTime(time())
            ->setActionType(\Smartling\AuditLog\Params\CreateRecordParameters::ACTION_TYPE_UPLOAD)
            ->setFileUri("file_uri")
            ->setFileUid("file_uid")
            ->setSourceLocaleId('en')
            ->setTargetLocaleIds(['de'])
            ->setTranslationJobUid("smartling_job_uid")
            ->setTranslationJobName("smartling_job_name")
            ->setTranslationJobDueDate("smartling_job_due_date")
            ->setTranslationJobAuthorize(true)
            ->setBatchUid("batch_uid")
            ->setDescription("description")
            ->setClientUserId("user_id")
            ->setClientUserEmail("user_email")
            ->setClientUserName("user_name")
            ->setEnvId("env_id")
            ->setClientData("foo", "bar");

        $response = $auditLog->createProjectLevelLogRecord($createParams);
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
 * @param string $accountUid
 * @return bool
 */
function createAccountLevelLogRecordDemo($authProvider, $projectId, $accountUid)
{
    $response = false;
    $auditLog = \Smartling\AuditLog\AuditLogApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $createParams = (new \Smartling\AuditLog\Params\CreateRecordParameters())
            ->setActionTime(time())
            ->setActionType(\Smartling\AuditLog\Params\CreateRecordParameters::ACTION_TYPE_UPLOAD)
            ->setFileUri("file_uri")
            ->setFileUid("file_uid")
            ->setSourceLocaleId('en')
            ->setTargetLocaleIds(['de'])
            ->setTranslationJobUid("smartling_job_uid")
            ->setTranslationJobName("smartling_job_name")
            ->setTranslationJobDueDate("smartling_job_due_date")
            ->setTranslationJobAuthorize(true)
            ->setBatchUid("batch_uid")
            ->setDescription("description")
            ->setClientUserId("user_id")
            ->setClientUserEmail("user_email")
            ->setClientUserName("user_name")
            ->setEnvId("env_id")
            ->setClientData("foo", "bar");

        $response = $auditLog->createAccountLevelLogRecord($accountUid, $createParams);
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

$response = createProjectLevelLogRecordDemo($authProvider, $projectId);
$response = createAccountLevelLogRecordDemo($authProvider, $projectId, $accountUid);
$response = searchProjectLevelLogRecordDemo($authProvider, $projectId);
$response = searchAccountLevelLogRecordDemo($authProvider, $projectId, $accountUid);
