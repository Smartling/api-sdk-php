<?php

/**
 * This file contains examples of Smartling API 2.x usage.
 *
 * How to use:
 * run "php example.php --project-id={PROJECT_ID} --user-id={USER_IDENTIFIER} --secret-key={SECRET_KEY}" in console
 *
 * Be sure you that dependencies are solved bu composer BEFORE running.
 */

use Smartling\Jobs\Params\AddFileToJobParameters;

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
 * @return bool
 */
function listJobsDemo($authProvider, $projectId)
{
    echo "--- List jobs ---\n";

    $response = [];
    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $params = new \Smartling\Jobs\Params\ListJobsParameters();
        $params->setStatuses([
            \Smartling\Jobs\JobStatus::AWAITING_AUTHORIZATION,
            \Smartling\Jobs\JobStatus::IN_PROGRESS,
        ]);
        $response = $jobs->listJobs($params);
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
 * @return string
 */
function createJobDemo($authProvider, $projectId)
{
    echo "--- Create job ---\n";

    $result = FALSE;
    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $params = new \Smartling\Jobs\Params\CreateJobParameters();
    $params->setName("Test Job Name " . time());
    $params->setDescription("Test Job Description " . time());
    $params->setDueDate(DateTime::createFromFormat('Y-m-d H:i:s', '2020-01-01 19:19:17', new DateTimeZone('UTC')));
    $params->setTargetLocales(['es', 'fr']);
    $st = microtime(true);

    try {
        $response = $jobs->createJob($params);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(true);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

    if (!empty($response)) {
        $result = $response['translationJobUid'];

        var_dump($response);
    }

    return $result;
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param string $jobId
 * @return string
 */
function updateJobDemo($authProvider, $projectId, $jobId)
{
    echo "--- Update job ---\n";

    $result = FALSE;
    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $params = new \Smartling\Jobs\Params\UpdateJobParameters();
    $params->setName("Test Job Name Updated " . time());
    $params->setDescription("Test Job Description Updated " . time());
    $params->setDueDate(DateTime::createFromFormat('Y-m-d H:i:s', '2030-01-01 19:19:17', new DateTimeZone('UTC')));
    $st = microtime(true);

    try {
        $response = $jobs->updateJob($jobId, $params);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(true);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

    if (!empty($response)) {
        $result = $response['translationJobUid'];

        var_dump($response);
    }

    return $result;
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param string $jobId
 * @return string
 */
function cancelJobDemo($authProvider, $projectId, $jobId)
{
    echo "--- Cancel job ---\n";

    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $params = new \Smartling\Jobs\Params\CancelJobParameters();
    $params->setReason('Some reason to cancel');
    $st = microtime(true);

    try {
        $jobs->cancelJobSync($jobId, $params);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(true);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param string $jobId
 * @return bool
 */
function getJobDemo($authProvider, $projectId, $jobId)
{
    echo "--- Get job ---\n";

    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $info = FALSE;
    $st = microtime(true);

    try {
        $info = $jobs->getJob($jobId);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(TRUE);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

    if (!empty($info)) {
        var_dump($info);
    }

    return $info;
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param string $fileUri
 * @return bool
 */
function searchJobDemo($authProvider, $projectId, $fileUri)
{
    echo "--- Search jobs ---\n";

    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $info = FALSE;
    $searchParameters = new \Smartling\Jobs\Params\SearchJobsParameters();
    $searchParameters->setFileUris([
        $fileUri,
    ]);
    $st = microtime(true);

    try {
        $info = $jobs->searchJobs($searchParameters);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(true);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

    if (!empty($info)) {
        var_dump($info['items']);
    }

    return $info;
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param string $jobId
 * @param string $fileUri
 * @return mixed
 */
function addFileToJobDemo($authProvider, $projectId, $jobId, $fileUri)
{
    echo "--- Add file to a job ---\n";

    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $params = new AddFileToJobParameters();
    $params->setFileUri($fileUri);
    $st = microtime(true);

    try {
        $jobs->addFileToJobSync($jobId, $params);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(true);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param string $jobId
 * @return bool
 */
function authorizeJobDemo($authProvider, $projectId, $jobId)
{
    echo "--- Authorize job ---\n";

    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $jobs->authorizeJob($jobId);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(true);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);
}

$fileUri = 'JobID1_en_fr.xml';
$jobs = listJobsDemo($authProvider, $projectId);
$jobId = createJobDemo($authProvider, $projectId);
$jobId = updateJobDemo($authProvider, $projectId, $jobId);
$job = getJobDemo($authProvider, $projectId, $jobId);
addFileToJobDemo($authProvider, $projectId, $jobId, $fileUri);
$job = searchJobDemo($authProvider, $projectId, $fileUri);
authorizeJobDemo($authProvider, $projectId, $jobId);
cancelJobDemo($authProvider, $projectId, $jobId);
