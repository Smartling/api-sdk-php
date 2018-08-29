<?php

namespace Smartling\TranslationRequests\Params;

/**
 * Class UpdateTranslationRequestParams
 * @package Smartling\TranslationRequests\Params
 */
class UpdateTranslationRequestParams extends TranslationRequestParamsAbstract
{
    /**
     * @param UpdateTranslationSubmissionParams $detail
     * @return $this
     */
    public function addDetail(UpdateTranslationSubmissionParams $detail)
    {
        if (!array_key_exists('details', $this->params)) {
            $this->set('details', []);
        }

        $this->params['details'][] = $detail->exportToArray();
        return $this;
    }

}
