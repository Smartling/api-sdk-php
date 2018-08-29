<?php

namespace Smartling\TranslationRequests\Params;

/**
 * Class UpdateTranslationRequestParams
 * @package Smartling\TranslationRequests\Params
 */
class UpdateTranslationRequestParams extends TranslationRequestParamsAbstract
{
    /**
     * @param UpdateTranslationSubmissionParams $translationSubmission
     * @return $this
     */
    public function addTranslationSubmission(UpdateTranslationSubmissionParams $translationSubmission)
    {
        if (!array_key_exists('translationSubmissions', $this->params)) {
            $this->set('translationSubmissions', []);
        }

        $this->params['translationSubmissions'][] = $translationSubmission->exportToArray();
        return $this;
    }

}
