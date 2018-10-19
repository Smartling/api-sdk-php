<?php

namespace Smartling\Context\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class UploadContextParameters
 * @package Context\Params
 */
class UploadContextParameters extends BaseParameters implements ContextParametersInterface
{

    /**
     * @param $contextFileUri
     *
     * @deprecated since version 3.5.0, to be removed in 4.0.0.
     * Use UploadContextParameters::setContent() instead.
     */
    public function setContextFileUri($contextFileUri)
    {
        $this->params['content'] = $contextFileUri;
    }

    public function setContent($contextFileUri)
    {
        $this->params['content'] = $contextFileUri;
    }

    public function setContentFileUri($contentFileUri) {
        $this->params['contentFileUri'] = $contentFileUri;
    }

    public function setName($name) {
        $this->params['name'] = $name;
    }

}
