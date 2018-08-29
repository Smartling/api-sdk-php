<?php

namespace Smartling\TranslationRequests\Params;

/**
 * Class UpdateTranslationSubmissionParams
 * @package Smartling\TranslationRequests\Params
 */
class UpdateTranslationSubmissionParams extends TranslationSubmissionParamsAbstract
{
    /**
     * @param string $translationSubmissionUid
     * @return $this
     */
    public function setTranslationSubmissionUid($translationSubmissionUid)
    {
        $this->set('translationSubmissionUid', (string)$translationSubmissionUid);
        return $this;
    }
}
