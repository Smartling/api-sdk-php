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
        if (!is_numeric($days)) {
            throw new \InvalidArgumentException('Days value must be numeric.');
        }

        $this->set('overrideContextOlderThanDays', $days);
    }
}
