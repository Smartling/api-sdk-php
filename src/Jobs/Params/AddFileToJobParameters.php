<?php

namespace Smartling\Jobs\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class AddFileToJobParameters
 * @package Jobs\Params
 */
class AddFileToJobParameters extends BaseParameters
{

    /**
     * @param string $fileUri
     */
    public function setFileUri($fileUri) {
        $this->params['fileUri'] = $fileUri;
    }

}
