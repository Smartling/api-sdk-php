<?php

namespace Smartling\Tests\Functional;

use PHPUnit_Framework_TestCase;
use Smartling\AuditLog\AuditLogApi;
use Smartling\AuditLog\Params\CreateRecordRecommendedParameters;
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
            $params = (new CreateRecordRecommendedParameters())
                ->setBucket('test_bucket_name')
                ->setTime(time())
                ->setActionType(CreateRecordRecommendedParameters::ACTION_TYPE_UPLOAD)
                ->setUserId($user_id)
                ->setDescription("test_description")
                ->setCustomField("my_custom_field", "test_custom_field_value");

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
            $params = (new CreateRecordRecommendedParameters())
                ->setBucket('test_bucket_name')
                ->setTime(time())
                ->setActionType(CreateRecordRecommendedParameters::ACTION_TYPE_UPLOAD)
                ->setUserId($user_id)
                ->setDescription("test_description")
                ->setCustomField("my_custom_field", "test_custom_field_value");

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

            $params = (new CreateRecordRecommendedParameters())
                ->setBucket('test_bucket_name')
                ->setTime(time())
                ->setActionType(CreateRecordRecommendedParameters::ACTION_TYPE_UPLOAD)
                ->setUserId($user_id)
                ->setDescription("test_description")
                ->setCustomField("my_custom_field", "test_custom_field_value");

            $this->auditLogApi->createProjectLevelLogRecord($params);

            sleep(1);

            $params = (new SearchRecordParameters())
                ->setSearchQuery("user_id:$user_id");

            $result = $this->auditLogApi->searchProjectLevelLogRecord($params);

            $this->assertArrayHasKey('totalCount', $result);
            $this->assertArrayHasKey('items', $result);

            $this->assertEquals($result['totalCount'], 1);
            $this->assertEquals(count($result['items']), 1);

            $this->assertArrayHasKey('time', $result['items'][0]);
            $this->assertArrayHasKey('bucket', $result['items'][0]);
            $this->assertArrayHasKey('action_type', $result['items'][0]);
            $this->assertArrayHasKey('user_id', $result['items'][0]);
            $this->assertArrayHasKey('description', $result['items'][0]);
            $this->assertArrayHasKey('my_custom_field', $result['items'][0]);
            $this->assertArrayHasKey('ip', $result['items'][0]);
            $this->assertArrayHasKey('host', $result['items'][0]);
            $this->assertArrayHasKey('gateway_request_id', $result['items'][0]);
            $this->assertArrayHasKey('browser', $result['items'][0]);
            $this->assertArrayHasKey('account_uid', $result['items'][0]);
            $this->assertArrayHasKey('project_uid', $result['items'][0]);

            $this->assertEquals($result['items'][0]['bucket'], 'test_bucket_name');
            $this->assertEquals($result['items'][0]['action_type'], CreateRecordRecommendedParameters::ACTION_TYPE_UPLOAD);
            $this->assertEquals($result['items'][0]['user_id'], $user_id);
            $this->assertEquals($result['items'][0]['description'], 'test_description');
            $this->assertEquals($result['items'][0]['my_custom_field'], 'test_custom_field_value');
            $this->assertEquals($result['items'][0]['account_uid'], getenv("account_uid"));
            $this->assertEquals($result['items'][0]['project_uid'], getenv('project_id'));
        } catch (SmartlingApiException $e) {
            $result = false;
        }

        return $result;
    }

    public function testSearchAccountLevelLogRecord()
    {
        try {
            $user_id = uniqid();

            $params = (new CreateRecordRecommendedParameters())
                ->setBucket('test_bucket_name')
                ->setTime(time())
                ->setActionType(CreateRecordRecommendedParameters::ACTION_TYPE_UPLOAD)
                ->setUserId($user_id)
                ->setDescription("test_description")
                ->setCustomField("my_custom_field", "test_custom_field_value");

            $this->auditLogApi->createAccountLevelLogRecord(getenv("account_uid"), $params);

            sleep(1);

            $params = (new SearchRecordParameters())
                ->setSearchQuery("user_id:$user_id");

            $result = $this->auditLogApi->searchAccountLevelLogRecord(getenv("account_uid"), $params);

            $this->assertArrayHasKey('totalCount', $result);
            $this->assertArrayHasKey('items', $result);

            $this->assertEquals($result['totalCount'], 1);
            $this->assertEquals(count($result['items']), 1);

            $this->assertArrayHasKey('time', $result['items'][0]);
            $this->assertArrayHasKey('bucket', $result['items'][0]);
            $this->assertArrayHasKey('action_type', $result['items'][0]);
            $this->assertArrayHasKey('user_id', $result['items'][0]);
            $this->assertArrayHasKey('description', $result['items'][0]);
            $this->assertArrayHasKey('my_custom_field', $result['items'][0]);
            $this->assertArrayHasKey('ip', $result['items'][0]);
            $this->assertArrayHasKey('host', $result['items'][0]);
            $this->assertArrayHasKey('gateway_request_id', $result['items'][0]);
            $this->assertArrayHasKey('browser', $result['items'][0]);
            $this->assertArrayHasKey('account_uid', $result['items'][0]);
            $this->assertArrayHasKey('project_uid', $result['items'][0]);

            $this->assertEquals($result['items'][0]['bucket'], 'test_bucket_name');
            $this->assertEquals($result['items'][0]['action_type'], CreateRecordRecommendedParameters::ACTION_TYPE_UPLOAD);
            $this->assertEquals($result['items'][0]['user_id'], $user_id);
            $this->assertEquals($result['items'][0]['description'], 'test_description');
            $this->assertEquals($result['items'][0]['my_custom_field'], 'test_custom_field_value');
            $this->assertEquals($result['items'][0]['account_uid'], getenv("account_uid"));
            $this->assertEquals($result['items'][0]['project_uid'], 'none');
        } catch (SmartlingApiException $e) {
            $result = false;
        }

        return $result;
    }
}
