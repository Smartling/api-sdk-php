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
 * @return bool
 */
function listJobsDemo($authProvider, $projectId)
{
    $response = [];
    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $response = $jobs->listJobs(new \Smartling\Jobs\Params\ListJobsParameters());
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(TRUE);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

    if (!empty($response)) {
        echo vsprintf('Total Jobs got: %s.%s', [$response['totalCount'], "\n\r"]);

        foreach ($response['items'] as $item) {
            echo vsprintf('Job "%s", UID(%s), dueDate is %s and has locales:%s.%s', [
                $item['jobName'],
                $item['translationJobUid'],
                $item['dueDate'],
                implode(',', $item['targetLocaleIds']),
                "\n\r"
            ]);
        }
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
        echo vsprintf('Created Job "%s", UID(%s), dueDate is %s with locales:%s.%s', [
            $response['jobName'],
            $response['translationJobUid'],
            $response['dueDate'],
            implode(',', $response['targetLocaleIds']),
            "\n\r"
        ]);

        $result = $response['translationJobUid'];
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
        echo vsprintf('Updated Job "%s", UID(%s), dueDate is %s with locales:%s.%s', [
            $response['jobName'],
            $response['translationJobUid'],
            $response['dueDate'],
            implode(',', $response['targetLocaleIds']),
            "\n\r"
        ]);

        $result = $response['translationJobUid'];
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
    $response = FALSE;
    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $params = new \Smartling\Jobs\Params\CancelJobParameters();
    $params->setReason('Some reason to cancel');
    $st = microtime(true);

    try {
        $response = $jobs->cancelJob($jobId, $params);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(true);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

    return $response;
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param string $jobId
 * @return bool
 */
function getJobDemo($authProvider, $projectId, $jobId)
{
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
        echo vsprintf('Got job "%s", UID(%s), dueDate is %s with locales:%s.%s', [
            $info['jobName'],
            $info['translationJobUid'],
            $info['dueDate'],
            implode(',', $info['targetLocaleIds']),
            "\n\r"
        ]);
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
      echo vsprintf('Found jobs:%s', ["\n\r"]);

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
    $response = FALSE;
    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $params = new AddFileToJobParameters();
    $params->setFileUri($fileUri);
    $st = microtime(true);

    try {
        $response = $jobs->addFileToJob($jobId, $params);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(true);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

    return $response;
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @param string $jobId
 * @return bool
 */
function authorizeJobDemo($authProvider, $projectId, $jobId)
{
    $response = FALSE;
    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $st = microtime(true);

    try {
        $response = $jobs->authorizeJob($jobId);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }

    $et = microtime(true);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);

    return $response;
}

$fileUri = 'JobID1_en_fr.xml';
$jobs = listJobsDemo($authProvider, $projectId);
$jobId = createJobDemo($authProvider, $projectId);
$jobId = updateJobDemo($authProvider, $projectId, $jobId);
$job = getJobDemo($authProvider, $projectId, $jobId);
$isFileAdded = addFileToJobDemo($authProvider, $projectId, $jobId, $fileUri);
$job = searchJobDemo($authProvider, $projectId, $fileUri);
$isAuthorized = authorizeJobDemo($authProvider, $projectId, $jobId);
$isCanceled = cancelJobDemo($authProvider, $projectId, $jobId);
