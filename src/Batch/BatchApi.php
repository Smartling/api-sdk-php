<?php

namespace Smartling\Batch;

use Psr\Log\LoggerInterface;
use Smartling\AuthApi\AuthApiInterface;
use Smartling\BaseApiAbstract;
use Smartling\Batch\Params\CreateBatchParameters;
use Smartling\Exceptions\SmartlingApiException;
use Smartling\File\Params\UploadFileParameters;

/**
 * Class JobsFacadeApi
 *
 * @package Smartling\Batch
 */
class BatchApi extends BaseApiAbstract
{

    const ACTION_EXECUTE = 'execute';
    const ENDPOINT_URL = 'https://api.smartling.com/jobs-batch-api/v1/projects';

    /**
     * Instantiates Jobs Facade API object.
     *
     * @param AuthApiInterface $authProvider
     * @param string $projectId
     * @param LoggerInterface $logger
     *
     * @return BatchApi
     */
    public static function create(AuthApiInterface $authProvider, $projectId, $logger = null)
    {

        $client = self::initializeHttpClient(self::ENDPOINT_URL);

        $instance = new self($projectId, $client, $logger, self::ENDPOINT_URL);
        $instance->setAuth($authProvider);

        return $instance;
    }

    /**
     * {@inheritdoc}
     */
    protected function processBodyOptions($requestData = []) {
        $opts = parent::processBodyOptions($requestData);
        $key = 'file';

        if (!empty($opts['multipart'])) {
            foreach ($opts['multipart'] as &$data) {
                if ($data['name'] == $key) {
                    $data['contents'] = $this->readFile($data['contents']);
                }
            }
        }

        return $opts;
    }

    /**
     * Creates a batch.
     *
     * @param CreateBatchParameters $parameters
     *
     * @return array
     *
     * @throws SmartlingApiException
     */
    public function createBatch(CreateBatchParameters $parameters)
    {
        $requestData = $this->getDefaultRequestData('json', $parameters->exportToArray());

        return $this->sendRequest('batches', $requestData, self::HTTP_METHOD_POST);
    }

    /**
     * Uploads file.
     *
     * @param $realPath
     * @param $fileName
     * @param $fileType
     * @param $batchUid
     * @param UploadFileParameters $parameters
     *
     * @return bool
     *
     * @throws SmartlingApiException
     */
    public function uploadBatchFile($realPath, $fileName, $fileType, $batchUid, UploadFileParameters $parameters = null) {
        // @TODO: let's pass file, fileUri and fileType in UploadFileParameters.
        // In this case we could get rid of passing these variables into this
        // method. But this approach requires changes in UploadFileParameters
        // class + changes in FileApi::uploadFile() method.
        if (is_null($parameters)) {
            $parameters = new UploadFileParameters();
        }
        $parameters = $parameters->exportToArray();
        $parameters['file'] = $realPath;
        $parameters['fileUri'] = $fileName;
        $parameters['fileType'] = $fileType;

        $endpoint = vsprintf('batches/%s/file', [$batchUid]);
        $requestData = $this->getDefaultRequestData('multipart', $parameters);

        return $this->sendRequest($endpoint, $requestData, self::HTTP_METHOD_POST);
    }

    /**
     * Execute batch.
     *
     * @param $batchUid
     *
     * @return bool
     *
     * @throws SmartlingApiException
     */
    public function executeBatch($batchUid)
    {
        $endpoint = vsprintf('batches/%s', [$batchUid]);
        $requestData = $this->getDefaultRequestData('json', [
          'action' => self::ACTION_EXECUTE,
        ]);

        return $this->sendRequest($endpoint, $requestData, self::HTTP_METHOD_POST);
    }

    /**
     * Returns batch status.
     *
     * @param $batchUid
     *
     * @return array
     *
     * @throws SmartlingApiException
     */
    public function getBatchStatus($batchUid) {
        $endpoint = vsprintf('batches/%s', [$batchUid]);
        $requestData = $this->getDefaultRequestData('query', []);

        return $this->sendRequest($endpoint, $requestData, self::HTTP_METHOD_GET);
    }

  /**
   * Returns list of batches.
   *
   * @return array
   *
   * @throws SmartlingApiException
   */
  public function listBatches() {
    $requestData = $this->getDefaultRequestData('query', []);

    return $this->sendRequest('batches', $requestData, self::HTTP_METHOD_GET);
  }

}
