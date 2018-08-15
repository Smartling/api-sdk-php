<?php

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
 * @param $projectId
 * @param $demoBucketName
 * @param $demoCreateParams
 * @return array
 */
function createSubmissionDemo($authProvider, $projectId, $demoBucketName, $demoCreateParams)
{
    echo "--- Create Submission ---\n";

    $response = [];
    $submissionApi = \Smartling\Submissions\SubmissionsApi::create($authProvider, $projectId);

    $st = microtime(true);

    try {
        $response = $submissionApi->createSubmission($demoBucketName, $demoCreateParams);
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
 * @param $projectId
 * @param $demoBucketName
 * @param $submissionUid
 * @param $demoUpdateParams
 * @return array|mixed
 */
function updateSubmissionDemo($authProvider, $projectId, $demoBucketName, $submissionUid, $demoUpdateParams)
{
    echo "--- Update Submission ---\n";

    $response = [];
    $submissionApi = \Smartling\Submissions\SubmissionsApi::create($authProvider, $projectId);

    $st = microtime(true);

    try {
        $response = $submissionApi->updateSubmission($demoBucketName, $submissionUid, $demoUpdateParams);
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
 * @param Smartling\AuthApi\AuthApiInterface $authProvider
 * @param $projectId
 * @param $demoBucketName
 * @param $submissionUid
 * @return array
 */
function getSubmissionDemo($authProvider, $projectId, $demoBucketName, $submissionUid)
{
    echo "--- Get Submission ---\n";

    $response = [];
    $submissionApi = \Smartling\Submissions\SubmissionsApi::create($authProvider, $projectId);

    $st = microtime(true);

    try {
        $response = $submissionApi->getSubmission($demoBucketName, $submissionUid);
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

function searchSubmissionDemo($authProvider, $projectId, $demoBucketName, $searchParams)
{
    echo "--- Search Submission ---\n";

    $response = [];
    $submissionApi = \Smartling\Submissions\SubmissionsApi::create($authProvider, $projectId);

    $st = microtime(true);

    try {
        $response = $submissionApi->searchSubmissions($demoBucketName, $searchParams);
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


$demoBucketName = 'tst-bucket';


$time = (string)microtime(true);

$demoCreateParams = (new \Smartling\Submissions\Params\CreateSubmissionParams())
    ->setOriginalAssetId(['a' => $time])
    ->setTitle(vsprintf('Submission %s', [$time]))
    ->setFileUri(vsprintf('/posts/hello-world_1_%s_post.xml', [$time]))
    ->setOriginalLocale('en-US');

$demoUpdateParams = (new \Smartling\Submissions\Params\UpdateSubmissionParams())
    ->setTitle('Updated Title');


$response = createSubmissionDemo($authProvider, $projectId, $demoBucketName, $demoCreateParams);
$submissionUid = $response['submission_uid'];
$response = updateSubmissionDemo($authProvider, $projectId, $demoBucketName, $submissionUid, $demoUpdateParams);

$response = getSubmissionDemo($authProvider, $projectId, $demoBucketName, $submissionUid);

$searchParams = (new \Smartling\Submissions\Params\SearchSubmissionsParams())
    ->setFileUri('%' . $time . '%');


$response = searchSubmissionDemo($authProvider, $projectId, $demoBucketName, $searchParams);
