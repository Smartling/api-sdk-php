<?php

namespace Smartling\TranslationRequests\Params;

/**
 * Class UpdateTranslationSubmissionParams
 * @package Smartling\TranslationRequests\Params
 */
class UpdateTranslationSubmissionParams extends TranslationSubmissionParamsAbstract
{
    /**
     * @param string $detailUid
     * @return $this
     */
    public function setDetailUid($detailUid)
    {
        $this->set('detail_uid', (string)$detailUid);
        return $this;
    }
}
