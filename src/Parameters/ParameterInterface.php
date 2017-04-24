<?php

namespace Smartling\Parameters;

/**
 * Interface ParameterInterface
 *
 * @package Smartling\Params
 */
interface ParameterInterface
{
    /**
     * Returns associative array with key => value pairs of set params
     *
     * @return array
     */
    public function exportToArray();

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function set($key, $value);
}
