<?php

namespace Smartling\Context\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class MatchContextParameters
 * @package Context\Params
 */
class MatchContextParameters extends BaseParameters implements ContextParametersInterface
{

    public function setContentFileUri($contentFileUri) {
        $this->params['contentFileUri'] = $contentFileUri;
    }

}
