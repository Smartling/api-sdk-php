<?php

namespace Smartling\ProgressTracker\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class RecordParameters
 * @package ProgressTracker\Params
 */
class RecordParameters extends BaseParameters
{
    /**
     * @param int $ttl
     */
    public function setTtl($ttl) {
        $this->set('ttl', $ttl);
    }

    /**
     * @param array $data
     */
    public function setData(array $data) {
        $this->set('data', $data);
    }
}
