<?php

namespace Smartling\Context\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class MissingResourcesParameters
 * @package Context\Params
 */
class MissingResourcesParameters extends BaseParameters
{

    public function setOffset($offset)
    {
        $this->params['offset'] = $offset;
    }

}
