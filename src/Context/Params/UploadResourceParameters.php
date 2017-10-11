<?php

namespace Smartling\Context\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class UploadResourceParameters
 * @package Context\Params
 */
class UploadResourceParameters extends BaseParameters
{

    public function setFile($resource) {
        $this->params['resource'] = $resource;
    }

}
