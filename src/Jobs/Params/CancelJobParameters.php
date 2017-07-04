<?php

namespace Smartling\Jobs\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class CancelJobParameters
 * @package Jobs\Params
 */
class CancelJobParameters extends BaseParameters
{

  /**
   * @param string $reason
   */
  public function setReason($reason) {
      $this->params['reason'] = $reason;
  }

}
