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
function createTranslationRequestDemo($authProvider, $projectId, $demoBucketName, $demoCreateParams)
{
    echo "--- Create Translation Request ---\n";

    $response = [];
    $translationRequestsApi = \Smartling\TranslationRequests\TranslationRequestsApi::create($authProvider, $projectId);

    $st = microtime(true);

    try {
        $response = $translationRequestsApi->createTranslationRequest($demoBucketName, $demoCreateParams);
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
 * @param $translationRequestUid
 * @param $demoUpdateParams
 * @return array|mixed
 */
function updateTranslationRequestDemo($authProvider, $projectId, $demoBucketName, $translationRequestUid, $demoUpdateParams)
{
    echo "--- Update Translation Request ---\n";

    $response = [];
    $translationRequestsApi = \Smartling\TranslationRequests\TranslationRequestsApi::create($authProvider, $projectId);

    $st = microtime(true);

    try {
        $response = $translationRequestsApi->updateTranslationRequest($demoBucketName, $translationRequestUid, $demoUpdateParams);
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
 * @param $translationRequestUid
 * @return array
 */
function getTranslationRequestDemo($authProvider, $projectId, $demoBucketName, $translationRequestUid)
{
    echo "--- Get Translation Request ---\n";

    $response = [];
    $translationRequestsApi = \Smartling\TranslationRequests\TranslationRequestsApi::create($authProvider, $projectId);

    $st = microtime(true);

    try {
        $response = $translationRequestsApi->getTranslationRequest($demoBucketName, $translationRequestUid);
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

function searchTranslationRequestDemo($authProvider, $projectId, $demoBucketName, $searchParams)
{
    echo "--- Search Translation Request ---\n";

    $response = [];
    $translationRequestsApi = \Smartling\TranslationRequests\TranslationRequestsApi::create($authProvider, $projectId);

    $st = microtime(true);

    try {
        $response = $translationRequestsApi->searchTranslationRequests($demoBucketName, $searchParams);
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

$demoCreateParams = (new \Smartling\TranslationRequests\Params\CreateTranslationRequestParams())
    ->setOriginalAssetKey(['a' => $time])
    ->setTitle(vsprintf('Submission %s', [$time]))
    ->setFileUri(vsprintf('/posts/hello-world_1_%s_post.xml', [$time]))
    ->setOriginalLocaleId('en-US');

$demoUpdateParams = (new \Smartling\TranslationRequests\Params\UpdateTranslationRequestParams())
    ->setTitle('Updated Title');


$response = createTranslationRequestDemo($authProvider, $projectId, $demoBucketName, $demoCreateParams);
$translationRequestUid = $response['translationRequestUid'];
$response = updateTranslationRequestDemo($authProvider, $projectId, $demoBucketName, $translationRequestUid, $demoUpdateParams);

$response = getTranslationRequestDemo($authProvider, $projectId, $demoBucketName, $translationRequestUid);

$searchParams = (new \Smartling\TranslationRequests\Params\SearchTranslationRequestParams())
    ->setFileUri('%' . $time . '%');


$response = searchTranslationRequestDemo($authProvider, $projectId, $demoBucketName, $searchParams);
