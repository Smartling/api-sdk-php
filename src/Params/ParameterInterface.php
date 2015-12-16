<?php

namespace Smartling\Params;

interface ParameterInterface {
  public function exportToArray();

  public function set($key, $value);
}
