<?php

namespace Smartling\TranslationRequests\Params;

/**
 * Class UpdateDetailParams
 * @package Smartling\TranslationRequests\Params
 */
class UpdateDetailParams extends DetailParamsAbstract
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
