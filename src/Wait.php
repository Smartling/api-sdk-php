<?php

namespace Smartling;

/**
 * Trait Wait
 * @package Smartling
 */
trait Wait {

    /**
     * Timeout for sync requests in seconds.
     *
     * @var int
     */
    private $timeOut = 15;

    /**
     * @return int
     */
    public function getTimeOut() {
        return $this->timeOut;
    }

    /**
     * @param int $timeOut
     */
    public function setTimeOut($timeOut) {
        if ($timeOut <= 0) {
            throw new \InvalidArgumentException('Timeout value must be more or grater then zero.');
        }

        $this->timeOut = $timeOut;
    }

}
