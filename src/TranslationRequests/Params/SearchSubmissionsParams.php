<?php

namespace Smartling\TranslationRequests\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class SearchSubmissionsParams
 * @package Smartling\TranslationRequests\Params
 */
class SearchSubmissionsParams extends BaseParameters
{
    /**
     * @param array $originalAssetId
     * @return $this
     */
    public function setOriginalAssetId(array $originalAssetId = [])
    {
        if (0 < count($originalAssetId)) {
            $this->set('original_asset_id', json_encode($originalAssetId));
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
            $this->set('custom_original_data', json_encode($customOriginalData));
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
            $this->set('translation_asset_id', json_encode($translationAssetId));
        }
        return $this;
    }

    /**
     * @param string $targetLocale
     * @return $this
     */
    public function setTargetLocale($targetLocale)
    {
        $this->set('target_locale', (string)$targetLocale);
        return $this;
    }

    /**
     * @param string $state
     * @return $this
     */
    public function setState($state)
    {
        $state = (string)$state;
        if (in_array($state, SubmissionDetailsStates::$allowedStates, true)) {
            $this->set('state', $state);
        }
        return $this;
    }

    /**
     * @param string $submitter
     * @return $this
     */
    public function setSubmitter($submitter)
    {
        $this->set('submitter', (string)$submitter);
        return $this;
    }

    /**
     * @param array $customTranslationData
     * @return $this
     */
    public function setCustomTranslationData(array $customTranslationData = [])
    {
        if (0 < count($customTranslationData)) {
            $this->set('custom_translation_data', json_encode($customTranslationData));
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

