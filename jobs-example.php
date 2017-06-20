<?php
$credentials = [
    'projectId' => '',
    'userId' => '',
    'userSecret' => '',
];

require_once 'vendor/autoload.php';

$authProvider = \Smartling\AuthApi\AuthTokenProvider::create($credentials['userId'], $credentials['userSecret']);

function listJobsDemo($authProvider, $projectId)
{
    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $st = microtime(true);
    $response = $jobs->listJobs(new \Smartling\Jobs\Params\ListJobsParameters());
    $et = microtime(true);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);
    if (is_array($response)) {
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
}

/**
 * @param \Smartling\AuthApi\AuthApiInterface $authProvider
 * @param string $projectId
 * @return string
 */
function createJobDemo($authProvider, $projectId)
{
    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $params = new \Smartling\Jobs\Params\CreateJobParameters();
    $params->setJobName("Test Job" . time());
    $params->setDueDate(DateTime::createFromFormat('Y-m-d H:i:s', '2020-01-01 19:19:17', new DateTimeZone('UTC')));
    $params->setTargetLocales(['es']);
    $st = microtime(true);
    try {
        $response = $jobs->createJob($params);
        $et = microtime(true);
        $time = $et - $st;
        echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);
        echo vsprintf('Created Job "%s", UID(%s), dueDate is %s with locales:%s.%s', [
            $response['jobName'],
            $response['translationJobUid'],
            $response['dueDate'],
            implode(',', $response['targetLocaleIds']),
            "\n\r"
        ]);
        return $response['translationJobUid'];
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }
}

function getJobDemo($authProvider, $projectId)
{
    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);

    $response = $jobs->listJobs(new \Smartling\Jobs\Params\ListJobsParameters());

    $jobList = $response['items'];
    shuffle($jobList);
    $job = reset($jobList);
    $randomJobUId = $job['translationJobUid'];
    $st = microtime(true);
    $info = $jobs->getJob($randomJobUId);
    $et = microtime(true);
    $time = $et - $st;

    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);
    echo vsprintf('Got random job "%s", UID(%s), dueDate is %s with locales:%s.%s', [
        $info['jobName'],
        $info['translationJobUid'],
        $info['dueDate'],
        implode(',', $info['targetLocaleIds']),
        "\n\r"
    ]);

}

function getRandomJobId($authProvider, $projectId)
{
    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $response = $jobs->listJobs(new \Smartling\Jobs\Params\ListJobsParameters());
    $jobList = $response['items'];
    var_dump($response);
    shuffle($jobList);
    $job = reset($jobList);
    $randomJobUId = $job['translationJobUid'];
    return $randomJobUId;
}

function addFileToJobDemo($authProvider, $projectId)
{
    $jobId = createJobDemo($authProvider, $projectId);
    $jobs = \Smartling\Jobs\JobsApi::create($authProvider, $projectId);
    $fileUri = '/blog/2017/06/02/test-aaa_post_1_298.xml';
    $st = microtime(true);
    try {
        $response = $jobs->addFileToJob($jobId, $fileUri);
    } catch (\Smartling\Exceptions\SmartlingApiException $e) {
        var_dump($e->getErrors());
    }
    $et = microtime(true);
    $time = $et - $st;
    echo vsprintf('Request took %s seconds.%s', [round($time, 3), "\n\r"]);
    return $jobId;
}

function authorizeJobDemo($authProvider, $projectId, $jobId)
{
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
}

listJobsDemo($authProvider, $credentials['projectId']);
//createJobDemo($authProvider, $credentials['projectId']);
getJobDemo($authProvider, $credentials['projectId']);
$jobId = addFileToJobDemo($authProvider, $credentials['projectId']);
authorizeJobDemo($authProvider, $credentials['projectId'], $jobId);


