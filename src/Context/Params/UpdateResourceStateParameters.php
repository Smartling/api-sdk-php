<?php

namespace Smartling\Context\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class UploadResourceParameters
 * @package Context\Params
 */
class UpdateResourceStateParameters extends BaseParameters
{
    const STATE_REGISTERED = 'REGISTERED';
    const STATE_FAILED = 'FAILED';

    public function setState($state) {
        $allowedStates = [UpdateResourceStateParameters::STATE_FAILED, UpdateResourceStateParameters::STATE_REGISTERED];

        if (!\in_array($state, $allowedStates)) {
            throw new \InvalidArgumentException('Allowed states are: ' . \implode(', ', $allowedStates));
        }

        $this->params['state'] = $state;

        return $this;
    }

}
