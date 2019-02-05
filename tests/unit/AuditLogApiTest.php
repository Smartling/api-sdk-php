<?php

namespace Smartling\Tests\Unit;

use Smartling\AuditLog\AuditLogApi;
use Smartling\AuditLog\Params\CreateRecordParameters;
use Smartling\AuditLog\Params\SearchRecordParameters;

class AuditLogApiTest extends ApiTestAbstract
{
    /**
     * @covers \Smartling\AuditLog\AuditLogApi::createProjectLevelLogRecord
     */
    public function testCreateProjectLevelLogRecord()
    {
        $endpointUrl = vsprintf(
            '%s/projects/%s/logs',
            [
                AuditLogApi::ENDPOINT_URL,
                $this->projectId,
            ]
        );

        $createParams = (new CreateRecordParameters())
            ->setActionTime(1234567890)
            ->setActionType(CreateRecordParameters::ACTION_TYPE_UPLOAD)
            ->setFileUri("file_uri")
            ->setFileUid("file_uid")
            ->setSourceLocaleId('en')
            ->setTargetLocaleIds(['de'])
            ->setTranslationJobUid("smartling_job_uid")
            ->setTranslationJobName("smartling_job_name")
            ->setTranslationJobDueDate("smartling_job_due_date")
            ->setTranslationJobAuthorize(1)
            ->setBatchUid("batch_uid")
            ->setDescription("description")
            ->setClientUserId("user_id")
            ->setClientUserEmail("user_email")
            ->setClientUserName("user_name")
            ->setEnvId("env_id")
            ->setClientData("foo", "bar")
            ->setClientData("foo1", "bar1");

        $this->client->expects($this->any())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'json' => [
                    'actionTime' => '2009-02-13T23:31:30Z',
                    'actionType' => CreateRecordParameters::ACTION_TYPE_UPLOAD,
                    'fileUri' => 'file_uri',
                    'fileUid' => 'file_uid',
                    'sourceLocaleId' => 'en',
                    'targetLocaleIds' => ['de'],
                    'translationJobUid' => 'smartling_job_uid',
                    'translationJobName' => 'smartling_job_name',
                    'translationJobDueDate' => 'smartling_job_due_date',
                    'translationJobAuthorize' => true,
                    'batchUid' => 'batch_uid',
                    'description' => 'description',
                    'clientUserId' => 'user_id',
                    'clientUserEmail' => 'user_email',
                    'clientUserName' => 'user_name',
                    'envId' => 'env_id',
                    'clientData' => [
                        'foo' => 'bar',
                        'foo1' => 'bar1',
                    ]
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->createProjectLevelLogRecord($createParams);
    }

    /**
     * @covers \Smartling\AuditLog\AuditLogApi::createAccountLevelLogRecord
     */
    public function testCreateAccountLevelLogRecord()
    {
        $accountUid = "account_uid";
        $endpointUrl = vsprintf(
            '%s/accounts/%s/logs',
            [
                AuditLogApi::ENDPOINT_URL,
                $accountUid,
            ]
        );

        $createParams = (new CreateRecordParameters())
            ->setActionTime(1234567890)
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
            ->setClientUserId("user_id")
            ->setClientUserEmail("user_email")
            ->setClientUserName("user_name")
            ->setEnvId("env_id")
            ->setClientData("foo", "bar")
            ->setClientData("foo1", "bar1");

        $this->client->expects($this->any())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'json' => [
                    'actionTime' => '2009-02-13T23:31:30Z',
                    'actionType' => CreateRecordParameters::ACTION_TYPE_UPLOAD,
                    'fileUri' => 'file_uri',
                    'fileUid' => 'file_uid',
                    'sourceLocaleId' => 'en',
                    'targetLocaleIds' => ['de'],
                    'translationJobUid' => 'smartling_job_uid',
                    'translationJobName' => 'smartling_job_name',
                    'translationJobDueDate' => 'smartling_job_due_date',
                    'translationJobAuthorize' => true,
                    'batchUid' => 'batch_uid',
                    'description' => 'description',
                    'clientUserId' => 'user_id',
                    'clientUserEmail' => 'user_email',
                    'clientUserName' => 'user_name',
                    'envId' => 'env_id',
                    'clientData' => [
                        'foo' => 'bar',
                        'foo1' => 'bar1',
                    ]
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->createAccountLevelLogRecord($accountUid, $createParams);
    }

    /**
     * @covers \Smartling\AuditLog\AuditLogApi::searchProjectLevelLogRecord
     */
    public function testSearchProjectLevelLogRecord()
    {
        $endpointUrl = vsprintf(
            '%s/projects/%s/logs',
            [
                AuditLogApi::ENDPOINT_URL,
                $this->projectId,
            ]
        );

        $createParams = (new SearchRecordParameters())
            ->setSearchQuery("foo:bar")
            ->setOffset(1)
            ->setLimit(100)
            ->setSort("baz", SearchRecordParameters::ORDER_ASC);

        $this->client->expects($this->any())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [
                    'q' => 'foo:bar',
                    'offset' => 1,
                    'limit' => 100,
                    'sort' => 'baz:' . SearchRecordParameters::ORDER_ASC,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->searchProjectLevelLogRecord($createParams);
    }

    /**
     * @covers \Smartling\AuditLog\AuditLogApi::searchAccountLevelLogRecord
     */
    public function testSearchAccountLevelLogRecord()
    {
        $accountUid = "account_uid";
        $endpointUrl = vsprintf(
            '%s/accounts/%s/logs',
            [
                AuditLogApi::ENDPOINT_URL,
                $accountUid,
            ]
        );

        $createParams = (new SearchRecordParameters())
            ->setSearchQuery("foo:bar")
            ->setOffset(1)
            ->setLimit(100)
            ->setSort("baz", SearchRecordParameters::ORDER_ASC);

        $this->client->expects($this->any())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [
                    'q' => 'foo:bar',
                    'offset' => 1,
                    'limit' => 100,
                    'sort' => 'baz:' . SearchRecordParameters::ORDER_ASC,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->searchAccountLevelLogRecord($accountUid, $createParams);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->prepareAuditLogApiMock();
    }

    private function prepareAuditLogApiMock()
    {
        $this->object = $this->getMockBuilder('Smartling\AuditLog\AuditLogApi')
            ->setMethods(NULL)
            ->setConstructorArgs([
                $this->projectId,
                $this->client,
                null,
                AuditLogApi::ENDPOINT_URL,
            ])
            ->getMock();

        $this->invokeMethod(
            $this->object,
            'setAuth',
            [
                $this->authProvider
            ]
        );
    }
}
