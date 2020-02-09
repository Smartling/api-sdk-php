<?php

namespace Smartling\Strings\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class GetSourceStringsParameters
 * @package Strings\Params
 */
class GetSourceStringsParameters extends BaseParameters
{

    /**
     * @param string $fileUri
     */
    public function setFileUri($fileUri) {
        $this->set('fileUri', $fileUri);
    }

}
