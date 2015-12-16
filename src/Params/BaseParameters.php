<?php

namespace Smartling\Params;

class BaseParameters implements ParameterInterface {
  protected $params = [];

  public function exportToArray() {
    return $this->params;
  }

  public function set($key, $value) {
    $this->params[$key] = $value;
  }
}
