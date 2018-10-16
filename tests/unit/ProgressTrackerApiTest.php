<?php

namespace Smartling\Tests\Unit;

use Smartling\ProgressTracker\Params\RecordParameters;
use Smartling\ProgressTracker\ProgressTrackerApi;

/**
 * Test class for Smartling\ProgressTracker\ProgressTrackerApi.
 */
class ProgressTrackerApiTest extends ApiTestAbstract
{
    /**
     * @covers \Smartling\ProgressTracker\ProgressTrackerApi::getToken
     */
    public function testGetToken()
    {
        $accountUid = "account_uid";
        $endpointUrl = vsprintf(
            '%s/accounts/%s/token',
            [
                ProgressTrackerApi::ENDPOINT_URL,
                $accountUid
            ]
        );

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
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $this->object->getToken($accountUid);
    }

    /**
     * @covers \Smartling\ProgressTracker\ProgressTrackerApi::createRecord
     */
    public function testCreateRecord()
    {
        $spaceId = "space";
        $objectId = "object";
        $ttl = 5;
        $data = [
          "foo" => "bar",
        ];
        $endpointUrl = vsprintf(
            '%s/projects/%s/spaces/%s/objects/%s/records',
            [
                ProgressTrackerApi::ENDPOINT_URL,
                $this->projectId,
                $spaceId,
                $objectId,
            ]
        );

        $params = new RecordParameters();
        $params->setTtl($ttl);
        $params->setData($data);

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
                  "ttl" => $ttl,
                  "data" => $data,
              ],
            ])
            ->willReturn($this->responseMock);

        $this->object->createRecord($spaceId, $objectId, $params);
    }

    /**
     * @covers \Smartling\ProgressTracker\ProgressTrackerApi::deleteRecord
     */
    public function testDeleteRecord()
    {
        $spaceId = "space";
        $objectId = "object";
        $recordId = "record";
        $endpointUrl = vsprintf(
            '%s/projects/%s/spaces/%s/objects/%s/records/%s',
            [
                ProgressTrackerApi::ENDPOINT_URL,
                $this->projectId,
                $spaceId,
                $objectId,
              $recordId
            ]
        );

        $this->client->expects($this->any())
            ->method('request')
            ->with('delete', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $this->object->deleteRecord($spaceId, $objectId, $recordId);
    }

    /**
     * @covers \Smartling\ProgressTracker\ProgressTrackerApi::updateRecord
     */
    public function testUpdateRecord()
    {
        $spaceId = "space";
        $objectId = "object";
        $recordId = "record";
        $ttl = 5;
        $data = [
            "foo" => "bar",
        ];
        $endpointUrl = vsprintf(
            '%s/projects/%s/spaces/%s/objects/%s/records/%s',
            [
                ProgressTrackerApi::ENDPOINT_URL,
                $this->projectId,
                $spaceId,
                $objectId,
                $recordId,
            ]
        );

        $params = new RecordParameters();
        $params->setTtl($ttl);
        $params->setData($data);

        $this->client->expects($this->any())
            ->method('request')
            ->with('put', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => false,
                'json' => [
                    "ttl" => $ttl,
                    "data" => $data,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->updateRecord($spaceId, $objectId, $recordId, $params);
    }

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        $this->prepareProgressTrackerApiMock();
    }

    private function prepareProgressTrackerApiMock()
    {
        $this->object = $this->getMockBuilder('Smartling\ProgressTracker\ProgressTrackerApi')
            ->setMethods(NULL)
            ->setConstructorArgs([
                $this->projectId,
                $this->client,
                null,
                ProgressTrackerApi::ENDPOINT_URL,
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
