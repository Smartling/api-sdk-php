<?php

namespace Smartling\Parameters;

/**
 * Class BaseParameters
 *
 * @package Smartling\Params
 */
class BaseParameters implements ParameterInterface
{

    /**
     * @var array
     */
    protected $params = [];

    /**
     * @inheritdoc
     */
    public function exportToArray()
    {
        return $this->params;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        $this->params[$key] = $value;
    }
}
