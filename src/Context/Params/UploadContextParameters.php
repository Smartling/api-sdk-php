<?php

namespace Smartling\Context\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class UploadContextParameters
 * @package Context\Params
 */
class UploadContextParameters extends BaseParameters
{
    public function setContent($contextFileUri)
    {
        $this->set('content', $contextFileUri);
    }

    public function setName($name)
    {
        $this->set('name', $name);
    }

    public function setMatchParams(MatchContextParameters $params)
    {
        $this->set('matchParams', \json_encode($params->exportToArray()));
    }
}
