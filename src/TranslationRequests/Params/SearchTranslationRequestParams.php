<?php

namespace Smartling\TranslationRequests\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class SearchTranslationRequestParams
 * @package Smartling\TranslationRequests\Params
 */
class SearchTranslationRequestParams extends BaseParameters
{
    /**
     * @param array $originalAssetId
     * @return $this
     */
    public function setOriginalAssetId(array $originalAssetId = [])
    {
        if (0 < count($originalAssetId)) {
            $this->set('originalAssetId', json_encode($originalAssetId));
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
        if (0 < count($customOriginalData)) {
            $this->set('customOriginalData', json_encode($customOriginalData));
        }
        return $this;
    }

    /**
     * @param array $translationAssetId
     * @return $this
     */
    public function setTranslationAssetId(array $translationAssetId = [])
    {
        if (0 < count($translationAssetId)) {
            $this->set('translationAssetId', json_encode($translationAssetId));
        }
        return $this;
    }

    /**
     * @param string $targetLocaleId
     * @return $this
     */
    public function setTargetLocaleId($targetLocaleId)
    {
        $this->set('targetLocaleId', (string)$targetLocaleId);
        return $this;
    }

    /**
     * @param string $state
     * @return $this
     */
    public function setState($state)
    {
        $state = (string)$state;
        if (in_array($state, TranslationSubmissionStates::$allowedStates, true)) {
            $this->set('state', $state);
        }
        return $this;
    }

    /**
     * @param string $submitterName
     * @return $this
     */
    public function setSubmitterName($submitterName)
    {
        $this->set('submitterName', (string)$submitterName);
        return $this;
    }

    /**
     * @param array $customTranslationData
     * @return $this
     */
    public function setCustomTranslationData(array $customTranslationData = [])
    {
        if (0 < count($customTranslationData)) {
            $this->set('customTranslationData', json_encode($customTranslationData));
        }

        return $this;
    }

    /**
     * @param int $limit
     * @return $this
     */
    public function setLimit($limit)
    {
        $this->set('limit', (int)$limit);
        return $this;
    }

    /**
     * @param int $offset
     * @return $this
     */
    public function setOffset($offset)
    {
        $this->set('offset', (int)$offset);
        return $this;
    }
}

