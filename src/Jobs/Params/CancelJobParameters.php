<?php

namespace Smartling\Jobs\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class CancelJobParameters
 * @package Jobs\Params
 */
class CancelJobParameters extends BaseParameters
{

    /**
     * @param string $reason
     */
    public function setReason($reason) {
        if (mb_strlen($reason, 'UTF-8') > 4096) {
            throw new \InvalidArgumentException('Reason should be less or equal to 4096 characters.');
        }

        $this->set('reason', $reason);
    }

}
