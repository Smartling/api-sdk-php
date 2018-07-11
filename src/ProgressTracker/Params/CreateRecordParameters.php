<?php

namespace Smartling\ProgressTracker\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class CreateRecordParameters
 * @package ProgressTracker\Params
 */
class CreateRecordParameters extends BaseParameters
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
