<?php

namespace Smartling\TranslationRequests\Params;

/**
 * Class CreateTranslationRequestParams
 * @package Smartling\TranslationRequests\Params
 */
class CreateTranslationRequestParams extends TranslationRequestParamsAbstract
{
    /**
     * @param string $translationRequestUid
     * @return $this
     */
    public function setTranslationRequestUid($translationRequestUid)
    {
        $this->set('translationRequestUid', (string)$translationRequestUid);
        return $this;
    }

    /**
     * @param array $originalAssetId
     * @return $this
     */
    public function setOriginalAssetId(array $originalAssetId = [])
    {
        $this->set('originalAssetId', json_encode($originalAssetId));
        return $this;
    }

    /**
     * @param $fileUri
     * @return $this
     */
    public function setFileUri($fileUri)
    {
        $this->set('fileUri', (string)$fileUri);
        return $this;
    }

    /**
     * @param $originalLocaleId
     * @return $this
     */
    public function setOriginalLocaleId($originalLocaleId)
    {
        $this->set('originalLocaleId', (string)$originalLocaleId);
        return $this;
    }

    /**
     * @param CreateTranslationSubmissionParams $translationSubmission
     * @return $this
     */
    public function addTranslationSubmission(CreateTranslationSubmissionParams $translationSubmission)
    {
        if (!array_key_exists('translationSubmissions', $this->params)) {
            $this->set('translationSubmissions', []);
        }

        $this->params['translationSubmissions'][] = $translationSubmission->exportToArray();
        return $this;
    }
}
