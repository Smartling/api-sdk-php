<?php

namespace Smartling\TranslationRequests\Params;

/**
 * Class UpdateSubmissionParams
 * @package Smartling\TranslationRequests\Params
 */
class UpdateSubmissionParams extends SubmissionParamsAbstract
{
    /**
     * @param UpdateDetailParams $detail
     * @return $this
     */
    public function addDetail(UpdateDetailParams $detail)
    {
        if (!array_key_exists('details', $this->params)) {
            $this->set('details', []);
        }

        $this->params['details'][] = $detail->exportToArray();
        return $this;
    }

}
