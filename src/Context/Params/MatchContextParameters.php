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

    public function setOverrideContextOlderThanDays($days)
    {
        if ($days < 0) {
            throw new \InvalidArgumentException('Days value must be grater or equal to zero.');
        }

        $this->set('overrideContextOlderThanDays', $days);
    }
}
