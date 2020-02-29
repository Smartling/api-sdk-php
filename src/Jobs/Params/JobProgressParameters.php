<?php

namespace Smartling\Jobs\Params;

use Smartling\Parameters\BaseParameters;

class JobProgressParameters extends BaseParameters
{

  public function setTargetLocaleId(string $localeId) {
    $this->set('targetLocaleId', $localeId);
  }

}
