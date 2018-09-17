<?php

namespace Smartling;

use Smartling\Exceptions\SmartlingApiException;

/**
 * Interface Waitable
 * @package Smartling
 */
interface Waitable {

    /**
     * Sets timeout.
     *
     * @param int $syncTimeOut
     * @return mixed
     */
    public function setTimeOut($syncTimeOut);

    /**
     * Returns timeout.
     *
     * @return int
     */
    public function getTimeOut();

    /**
     * Makes async operation sync.
     *
     * @param array $data
     * @throws SmartlingApiException
     */
    public function wait(array $data);

}
