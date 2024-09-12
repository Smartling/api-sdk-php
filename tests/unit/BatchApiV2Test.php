<?php

namespace Smartling\Tests\Unit;

use Smartling\Batch\BatchApiV2;
use Smartling\Exceptions\SmartlingApiException;

class BatchApiV2Test extends ApiTestAbstract
{
    /**
     * @return mixed
     * @throws \Throwable
     */
    private function throwPreviousException(callable $callable)
    {
        try {
            return $callable();
        } catch (SmartlingApiException $e) {
            throw $e->getPrevious();
        }
    }

    public function testCreateBatch()
    {
        $jobId = 'test_job_id';
        $authorize = true;
        $fileUris = ['fileUri'];

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('post', BatchApiV2::ENDPOINT_URL . "/$this->projectId/batches", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => "{$this->authProvider->getTokenType()} {$this->authProvider->getAccessToken()}",
                ],
                'exceptions' => false,
                'json' => [
                    'translationJobUid' => $jobId,
                    'authorize' => $authorize,
                    'fileUris' => $fileUris,
                ],
            ])
            ->willReturn($this->getResponse(json_encode(['response' => ['code' => 'SUCCESS', 'data' => ['batchUid' => 'batchuid']]])));

        $this->throwPreviousException(function () use ($authorize, $jobId, $fileUris) {
            $this->assertEquals('batchuid', (new BatchApiV2($this->authProvider, $this->projectId, null, $this->client))
                ->createBatch($authorize, $jobId, $fileUris));
        });
    }

    public function testCancelBatchAction()
    {
        $batchId = 'test_batch';
        $fileUri = '/file_uri';
        $reason = 'Test';

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('put', BatchApiV2::ENDPOINT_URL . "/$this->projectId/batches/$batchId", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => \vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'json' => [
                    'action' => 'CANCEL_FILE',
                    'fileUri' => '/file_uri',
                    'reason' => 'Test',
                ],
            ])
            ->willReturn($this->responseMock);

        $this->throwPreviousException(function () use ($batchId, $fileUri, $reason) {
            (new BatchApiV2($this->authProvider, $this->projectId, null, $this->client))
                ->cancelBatchFile($batchId, $fileUri, $reason);
        });
    }

    public function testBatchNotCreatedEmptyFileUris()
    {
        $this->expectException(\UnexpectedValueException::class);
        (new BatchApiV2($this->authProvider, $this->projectId, null, $this->client))->createBatch(true, 'jobUid', []);
    }

    public function testBatchNotCreatedFileUrisNotList()
    {
        $this->expectException(\UnexpectedValueException::class);
        (new BatchApiV2($this->authProvider, $this->projectId, null, $this->client))->createBatch(true, 'jobUid', [
            1 => 'fileUri',
        ]);
    }

    public function testArrayIsListPolyfill()
    {
        $this->assertTrue(BatchApiV2::array_is_list([]));
        $this->assertTrue(BatchApiV2::array_is_list(['a', 'b', 'c']));
        $this->assertTrue(BatchApiV2::array_is_list(array_unique(['a', 'b', 'c'])));
        $this->assertTrue(BatchApiV2::array_is_list(array_unique([0 => 'a', 1 => 'b', 2 => 'c'])));
        $this->assertTrue(BatchApiV2::array_is_list(array_values(array_unique(['a', 'b', 'b', 'c']))));
        $this->assertFalse(BatchApiV2::array_is_list(array_unique(['a', 'b', 'b', 'c'])));
        $this->assertFalse(BatchApiV2::array_is_list([1 => 'a']));
    }
}
