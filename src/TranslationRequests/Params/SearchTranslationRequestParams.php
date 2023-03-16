<?php

namespace Smartling\TranslationRequests\Params;

/**
 * Class SearchTranslationRequestParams
 * @package Smartling\TranslationRequests\Params
 */
class SearchTranslationRequestParams extends SearchTranslationSubmissionParams
{
    const ORDER_DESC = "desc";
    const ORDER_ASC = "asc";
    const SORT_CREATED = "createdDate";
    const SORT_MODIFIED = "modifiedDate";

    /**
     * @param array $originalAssetKey
     * @return $this
     */
    public function setOriginalAssetKey(array $originalAssetKey = [])
    {
        if (0 < \count($originalAssetKey)) {
            $this->set('originalAssetKey', \json_encode($originalAssetKey));
        }
        return $this;
    }

    /**
     * @param string $fileUri
     * @return $this
     */
    public function setFileUri($fileUri)
    {
        $this->set('fileUri', (string)$fileUri);
        return $this;
    }

    /**
     * @param int $outdated
     * @return $this
     */
    public function setOutdated($outdated)
    {
        $this->set('outdated', (int)$outdated);
        return $this;
    }

    /**
     * @param array $customOriginalData
     * @return $this
     */
    public function setCustomOriginalData(array $customOriginalData = [])
    {
        if (0 < \count($customOriginalData)) {
            $this->set('customOriginalData', \json_encode($customOriginalData));
        }
        return $this;
    }

    public function setWithBatchUid()
    {
        $this->set('withBatchUid', 1);
        return $this;
    }

    public function setWithoutBatchUid()
    {
        $this->set('withoutBatchUid', 1);
        return $this;
    }
}
