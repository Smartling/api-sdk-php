<?php

namespace Smartling\Tests\Unit;

use Smartling\Batch\BatchApiV2;
use Smartling\Batch\Params\ListBatchesParameters;
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
            ->willReturn($this->responseMock);

        $this->throwPreviousException(function () use ($authorize, $jobId, $fileUris) {
            (new BatchApiV2($this->authProvider, $this->projectId, null, $this->client))
                ->createBatch($authorize, $jobId, $fileUris);
        });
    }

    public function testListBatches()
    {
        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('get', BatchApiV2::ENDPOINT_URL . "/$this->projectId/batches", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => \vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'query' => [
                    'translationJobUid' => 'jobUid',
                    'status' => 'COMPLETED',
                    'sortBy' => 'status',
                    'orderBy' => 'desc',
                    'offset' => 5,
                    'limit' => 7,
                ],
            ])
            ->willReturn($this->responseMock);

        $this->throwPreviousException(function () {
            (new BatchApiV2($this->authProvider, $this->projectId, null, $this->client))
                ->listBatches((new ListBatchesParameters())
                    ->setTranslationJobUid('jobUid')
                    ->setStatus('COMPLETED')
                    ->setSortBy('status')
                    ->setOrderBy('desc')
                    ->setOffset(5)
                    ->setLimit(7));
        });
    }

    public function testGetBatchStatus()
    {
        $batchId = 'test_batch_id';

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('get', BatchApiV2::ENDPOINT_URL . "/$this->projectId/batches/$batchId", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => \vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'query' => [],
            ])
            ->willReturn($this->responseMock);

        $this->throwPreviousException(function () use ($batchId) {
            (new BatchApiV2($this->authProvider, $this->projectId, null, $this->client))
                ->getBatchStatus($batchId);
        });
    }

    public function testProcessBatchAction()
    {
        $batchId = 'test_batch';
        $action = 'REGISTER_FILE';
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
                    'action' => 'REGISTER_FILE',
                    'fileUri' => '/file_uri',
                    'reason' => 'Test',
                ],
            ])
            ->willReturn($this->responseMock);

        $this->throwPreviousException(function () use ($action, $batchId, $fileUri, $reason) {
            (new BatchApiV2($this->authProvider, $this->projectId, null, $this->client))
                ->processBatchAction($batchId, $action, $fileUri, $reason);
        });
    }
    public function testUploadFileToABatch()
    {
        $batchId = 'test_batch';
        $localeIdsToAuthorize = ['fr-FR', 'uk-UA'];
        $file = '<xml/>';
        $fileType = 'xml';
        $fileUri = '/file_uri';

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with('post', BatchApiV2::ENDPOINT_URL . "/$this->projectId/batches/$batchId/file", [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => \vsprintf('%s %s', [
                        $this->authProvider->getTokenType(),
                        $this->authProvider->getAccessToken(),
                    ]),
                ],
                'exceptions' => FALSE,
                'multipart' => [
                    ['name' => 'file', 'contents' => $file],
                    ['name' => 'fileUri', 'contents' => $fileUri],
                    ['name' => 'fileType', 'contents' => $fileType],
                    ['name' => 'localeIdsToAuthorize[]', 'contents' => $localeIdsToAuthorize[0]],
                    ['name' => 'localeIdsToAuthorize[]', 'contents' => $localeIdsToAuthorize[1]],
                ],
            ])
            ->willReturn($this->responseMock);

        $this->throwPreviousException(function () use ($batchId, $file, $fileUri, $fileType, $localeIdsToAuthorize) {
            (new BatchApiV2($this->authProvider, $this->projectId, null, $this->client))
                ->uploadFileToABatch($batchId, $file, $fileUri, $fileType, $localeIdsToAuthorize);
        });
    }
}
