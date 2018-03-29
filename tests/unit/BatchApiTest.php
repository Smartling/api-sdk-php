<?php

namespace Smartling\Tests\Unit;
use Smartling\Batch\BatchApi;
use Smartling\Batch\Params\CreateBatchParameters;
use Smartling\File\Params\UploadFileParameters;

/**
 * Test class for Smartling\Batch\BatchApi.
 */
class BatchApiTest extends ApiTestAbstract
{

    /**
     * {@inheritdoc}
     */
    protected function setUp() {
        parent::setUp();
        $this->prepareBatchApiMock();
    }

    /**
     * Sets up api mock object.
     */
    private function prepareBatchApiMock() {
        $this->object = $this->getMockBuilder('Smartling\Batch\BatchApi')
            ->setMethods(['readFile'])
            ->setConstructorArgs([
                $this->projectId,
                $this->client,
                null,
                BatchApi::ENDPOINT_URL,
            ])
            ->getMock();

        $this->object->expects(self::any())
            ->method('readFile')
            ->willReturn($this->streamPlaceholder);

        $this->invokeMethod(
            $this->object,
            'setAuth',
            [
                $this->authProvider
            ]
        );
    }

    /**
     * @covers \Smartling\Batch\BatchApi::createBatch
     */
    public function testCreateBatch() {
        $jobId = 'test_job_id';
        $authorize = false;
        $callbackUrl = 'test_callback_url';

        $params = new CreateBatchParameters();
        $params->setTranslationJobUid($jobId);
        $params->setAuthorize($authorize);
        $params->setCallbackUrl($callbackUrl);

        $endpointUrl = vsprintf('%s/%s/batches', [
            BatchApi::ENDPOINT_URL,
            $this->projectId
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'json' => [
                    'translationJobUid' => $jobId,
                    'authorize' => $authorize,
                    'callbackUrl' => $callbackUrl,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->createBatch($params);
    }

    /**
     * @covers \Smartling\Batch\BatchApi::uploadBatchFile
     */
    public function testUploadBatchFile() {
        $batchId = 'test_batch_id';
        $localesToApprove = 'fr-FR';
        $fileRealPath = realpath(__DIR__ . '/../resources/test.xml');
        $fileUri = 'test-file-uri.xml';
        $extension = 'xml';

        $params = new UploadFileParameters();
        $params->setLocalesToApprove($localesToApprove);

        $endpointUrl = vsprintf('%s/%s/batches/%s/file', [
            BatchApi::ENDPOINT_URL,
            $this->projectId,
            $batchId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'multipart' => [
                    [
                        'name' => 'authorize',
                        'contents' => 0,
                    ],
                    [
                        'name' => 'smartling.client_lib_id',
                        'contents' => json_encode(
                            [
                                'client' => BatchApi::CLIENT_LIB_ID_SDK,
                                'version' => BatchApi::CLIENT_LIB_ID_VERSION,
                            ],
                            JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE
                        ),
                    ],
                    [
                        'name' => 'localeIdsToAuthorize[]',
                        'contents' => $localesToApprove,
                    ],
                    [
                        'name' => 'file',
                        'contents' => $this->streamPlaceholder,
                    ],
                    [
                        'name' => 'fileUri',
                        'contents' => $fileUri,
                    ],
                    [
                        'name' => 'fileType',
                        'contents' => $extension,
                    ],
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->uploadBatchFile($fileRealPath, $fileUri, $extension, $batchId, $params);
    }

    /**
     * @covers \Smartling\Batch\BatchApi::executeBatch
     */
    public function testExecuteBatch() {
        $batchId = 'test_batch_id';

        $endpointUrl = vsprintf('%s/%s/batches/%s', [
            BatchApi::ENDPOINT_URL,
            $this->projectId,
            $batchId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('post', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'json' => [
                  'action' => BatchApi::ACTION_EXECUTE,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->object->executeBatch($batchId);
    }

    /**
     * @covers \Smartling\Batch\BatchApi::getBatchStatus
     */
    public function testGetBatchStatus() {
        $batchId = 'test_batch_id';

        $endpointUrl = vsprintf('%s/%s/batches/%s', [
            BatchApi::ENDPOINT_URL,
            $this->projectId,
            $batchId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $this->object->getBatchStatus($batchId);
    }

    /**
     * @covers \Smartling\Batch\BatchApi::listBatches
     */
    public function testListBatches() {
        $endpointUrl = vsprintf('%s/%s/batches', [
            BatchApi::ENDPOINT_URL,
            $this->projectId,
        ]);

        $this->client
            ->expects(self::once())
            ->method('request')
            ->with('get', $endpointUrl, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $this->object->listBatches();
    }

}
