<?php

namespace Smartling\Context\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class UploadContextParameters
 * @package Context\Params
 */
class UploadContextParameters extends BaseParameters
{

    public function setContextFileUri($contextFileUri)
    {
        $this->params['content'] = $contextFileUri;
    }

    public function setName($name) {
        $this->params['name'] = $name;
    }

}
