<?php

namespace Smartling\Submissions\Params;

use Smartling\Parameters\BaseParameters;

class SearchSubmissionsParams extends BaseParameters
{

    public function setOriginalAssetId(array $statement)
    {
        $this->set('original_asset_id', json_encode($statement));
        return $this;
    }

    public function setFileUri($fileUri)
    {
        $this->set('fileUri', $fileUri);
        return $this;
    }

    public function setOutdated($outdated)
    {
        $this->set('outdated', (int) $outdated);
        return $this;
    }

    public function setCustomOriginalData($customOriginalData)
    {
        $this->set('custom_original_data', json_encode($customOriginalData));
        return $this;
    }

    public function setTranslationAssetId($translationAssetId)
    {
        $this->set('translation_asset_id', json_encode($translationAssetId));
        return $this;
    }

    public function setTargetLocale($targetLocale)
    {
        $this->set('target_locale', $targetLocale);
        return $this;
    }

    public function setState($state)
    {
        $this->set('state', $state);
        return $this;
    }

    public function setSubmitter($submitter)
    {
        $this->set('submitter', $submitter);
        return $this;
    }

    public function setCustomTranslationData($customTranslationData)
    {
        $this->set('custom_translation_data', json_encode($customTranslationData));
        return $this;
    }

    public function setLimit($limit)
    {
        $this->set('limit', (int) $limit);
        return $this;
    }

    public function setOffset($offset)
    {
        $this->set('offset', (int) $offset);
        return $this;
    }
}
