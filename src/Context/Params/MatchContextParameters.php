<?php

namespace Smartling\Context\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class MatchContextParameters
 * @package Context\Params
 */
class MatchContextParameters extends BaseParameters
{

    public function setContentFileUri($contentFileUri)
    {
        $this->set('contentFileUri', $contentFileUri);
    }

}
