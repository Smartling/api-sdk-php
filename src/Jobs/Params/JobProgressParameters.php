<?php

namespace Smartling\Jobs\Params;

use Smartling\Parameters\BaseParameters;

class JobProgressParameters extends BaseParameters
{

  /**
   * @param string $localeId
   */
  public function setTargetLocaleId($localeId) {
    $this->set('targetLocaleId', $localeId);
  }

}
