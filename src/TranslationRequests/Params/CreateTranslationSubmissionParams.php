<?php

namespace Smartling\TranslationRequests\Params;

/**
 * Class CreateTranslationSubmissionParams
 * @package TranslationRequests\Params
 */
class CreateTranslationSubmissionParams extends TranslationSubmissionParamsAbstract
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
     * @param $targetLocaleId
     * @return $this
     */
    public function setTargetLocaleId($targetLocaleId)
    {
        $this->set('targetLocaleId', (string)$targetLocaleId);
        return $this;
    }
}
