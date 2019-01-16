<?php

namespace Smartling\Tests\Functional;

use PHPUnit_Framework_TestCase;
use Smartling\AuditLog\AuditLogApi;
use Smartling\AuditLog\Params\CreateRecordParameters;
use Smartling\AuditLog\Params\SearchRecordParameters;
use Smartling\AuthApi\AuthTokenProvider;
use Smartling\Exceptions\SmartlingApiException;

class AuditLogApiFunctionalTest extends PHPUnit_Framework_TestCase
{

    private $auditLogApi;

    public function setUp()
    {
        $projectId = getenv('project_id');
        $userIdentifier = getenv('user_id');
        $userSecretKey = getenv('user_key');

        if (
            empty($projectId) ||
            empty($userIdentifier) ||
            empty($userSecretKey)
        ) {
            $this->fail('Missing required parameters');
        }

        $authProvider = AuthTokenProvider::create($userIdentifier, $userSecretKey);
        $this->auditLogApi = AuditLogApi::create($authProvider, $projectId);
    }

    public function testCreateProjectLevelLogRecord()
    {
        try {
            $user_id = uniqid();
            $params = (new CreateRecordParameters())
                ->setActionTime(time())
                ->setActionType(CreateRecordParameters::ACTION_TYPE_UPLOAD)
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
                ->setClientUserId($user_id)
                ->setClientUserEmail("user_email")
                ->setClientUserName("user_name")
                ->setEnvId("env_id")
                ->setClientData("foo", "bar");

            $result = $this->auditLogApi->createProjectLevelLogRecord($params);

            $this->assertArrayHasKey('_index', $result);
            $this->assertArrayHasKey('_type', $result);
            $this->assertArrayHasKey('_id', $result);
            $this->assertArrayHasKey('_seq_no', $result);
        } catch (SmartlingApiException $e) {
            $result = false;
        }

        return $result;
    }

    public function testCreateAccountLevelLogRecord()
    {
        try {
            $user_id = uniqid();
            $params = (new CreateRecordParameters())
                ->setActionTime(time())
                ->setActionType(CreateRecordParameters::ACTION_TYPE_UPLOAD)
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
                ->setClientUserId($user_id)
                ->setClientUserEmail("user_email")
                ->setClientUserName("user_name")
                ->setEnvId("env_id")
                ->setClientData("foo", "bar");

            $result = $this->auditLogApi->createAccountLevelLogRecord(getenv("account_uid"), $params);

            $this->assertArrayHasKey('_index', $result);
            $this->assertArrayHasKey('_type', $result);
            $this->assertArrayHasKey('_id', $result);
            $this->assertArrayHasKey('_seq_no', $result);
        } catch (SmartlingApiException $e) {
            $result = false;
        }

        return $result;
    }

    public function testSearchProjectLevelLogRecord()
    {
        try {
            $user_id = uniqid();
            $time = time();

            $createParams = (new CreateRecordParameters())
                ->setActionTime($time)
                ->setActionType(CreateRecordParameters::ACTION_TYPE_UPLOAD)
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
                ->setClientUserId($user_id)
                ->setClientUserEmail("user_email")
                ->setClientUserName("user_name")
                ->setEnvId("env_id")
                ->setClientData("foo", "bar")
                ->setClientData("foo1", "bar1");

            $createParamsArray = $createParams->exportToArray();

            $this->auditLogApi->createProjectLevelLogRecord($createParams);

            sleep(1);

            $params = (new SearchRecordParameters())
                ->setSearchQuery("clientUserId:$user_id");

            $result = $this->auditLogApi->searchProjectLevelLogRecord($params);

            $this->assertArrayHasKey('totalCount', $result);
            $this->assertArrayHasKey('items', $result);

            $this->assertEquals($result['totalCount'], 1);
            $this->assertEquals(count($result['items']), 1);

            $this->assertEquals($result['items'][0]['actionTime'], $createParamsArray['actionTime']);
            $this->assertEquals($result['items'][0]['actionType'], $createParamsArray['actionType']);
            $this->assertEquals($result['items'][0]['fileUri'], $createParamsArray['fileUri']);
            $this->assertEquals($result['items'][0]['fileUid'], $createParamsArray['fileUid']);
            $this->assertEquals($result['items'][0]['sourceLocaleId'], $createParamsArray['sourceLocaleId']);
            $this->assertEquals($result['items'][0]['targetLocaleIds'], $createParamsArray['targetLocaleIds']);
            $this->assertEquals($result['items'][0]['translationJobUid'], $createParamsArray['translationJobUid']);
            $this->assertEquals($result['items'][0]['translationJobName'], $createParamsArray['translationJobName']);
            $this->assertEquals($result['items'][0]['translationJobDueDate'], $createParamsArray['translationJobDueDate']);
            $this->assertEquals($result['items'][0]['translationJobAuthorize'], $createParamsArray['translationJobAuthorize']);
            $this->assertEquals($result['items'][0]['batchUid'], $createParamsArray['batchUid']);
            $this->assertEquals($result['items'][0]['description'], $createParamsArray['description']);
            $this->assertEquals($result['items'][0]['clientUserId'], $createParamsArray['clientUserId']);
            $this->assertEquals($result['items'][0]['clientUserEmail'], $createParamsArray['clientUserEmail']);
            $this->assertEquals($result['items'][0]['clientUserName'], $createParamsArray['clientUserName']);
            $this->assertEquals($result['items'][0]['envId'], $createParamsArray['envId']);
            $this->assertEquals($result['items'][0]['clientData'], $createParamsArray['clientData']);
            $this->assertEquals($result['items'][0]['accountUid'], getenv("account_uid"));
            $this->assertEquals($result['items'][0]['projectUid'], getenv('project_id'));
        } catch (SmartlingApiException $e) {
            $result = false;
        }

        return $result;
    }

    public function testSearchAccountLevelLogRecord()
    {
        try {
            $user_id = uniqid();
            $time = time();

            $createParams = (new CreateRecordParameters())
                ->setActionTime($time)
                ->setActionType(CreateRecordParameters::ACTION_TYPE_UPLOAD)
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
                ->setClientUserId($user_id)
                ->setClientUserEmail("user_email")
                ->setClientUserName("user_name")
                ->setEnvId("env_id")
                ->setClientData("foo", "bar")
                ->setClientData("foo1", "bar1");

            $createParamsArray = $createParams->exportToArray();

            $this->auditLogApi->createAccountLevelLogRecord(getenv("account_uid"), $createParams);

            sleep(1);

            $params = (new SearchRecordParameters())
                ->setSearchQuery("clientUserId:$user_id");

            $result = $this->auditLogApi->searchAccountLevelLogRecord(getenv("account_uid"), $params);

            $this->assertArrayHasKey('totalCount', $result);
            $this->assertArrayHasKey('items', $result);

            $this->assertEquals($result['totalCount'], 1);
            $this->assertEquals(count($result['items']), 1);

            $this->assertEquals($result['items'][0]['actionTime'], $createParamsArray['actionTime']);
            $this->assertEquals($result['items'][0]['actionType'], $createParamsArray['actionType']);
            $this->assertEquals($result['items'][0]['fileUri'], $createParamsArray['fileUri']);
            $this->assertEquals($result['items'][0]['fileUid'], $createParamsArray['fileUid']);
            $this->assertEquals($result['items'][0]['sourceLocaleId'], $createParamsArray['sourceLocaleId']);
            $this->assertEquals($result['items'][0]['targetLocaleIds'], $createParamsArray['targetLocaleIds']);
            $this->assertEquals($result['items'][0]['translationJobUid'], $createParamsArray['translationJobUid']);
            $this->assertEquals($result['items'][0]['translationJobName'], $createParamsArray['translationJobName']);
            $this->assertEquals($result['items'][0]['translationJobDueDate'], $createParamsArray['translationJobDueDate']);
            $this->assertEquals($result['items'][0]['translationJobAuthorize'], $createParamsArray['translationJobAuthorize']);
            $this->assertEquals($result['items'][0]['batchUid'], $createParamsArray['batchUid']);
            $this->assertEquals($result['items'][0]['description'], $createParamsArray['description']);
            $this->assertEquals($result['items'][0]['clientUserId'], $createParamsArray['clientUserId']);
            $this->assertEquals($result['items'][0]['clientUserEmail'], $createParamsArray['clientUserEmail']);
            $this->assertEquals($result['items'][0]['clientUserName'], $createParamsArray['clientUserName']);
            $this->assertEquals($result['items'][0]['envId'], $createParamsArray['envId']);
            $this->assertEquals($result['items'][0]['clientData'], $createParamsArray['clientData']);
            $this->assertEquals($result['items'][0]['accountUid'], getenv("account_uid"));
            $this->assertEquals($result['items'][0]['projectUid'], 'none');
        } catch (SmartlingApiException $e) {
            $result = false;
        }

        return $result;
    }
}
