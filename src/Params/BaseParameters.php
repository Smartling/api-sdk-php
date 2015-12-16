<?php

namespace Smartling\Params;

class BaseParameters implements ParameterInterface {
  protected $params = [];

  public function exportToArray() {
    return $this->params;
  }
}
