<?php

namespace Smartling\Jobs\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class SearchJobsParameters
 *
 * @package Smartling\Params
 */
class SearchJobsParameters extends BaseParameters
{

  /**
   * Sets hash codes for searching.
   *
   * @param array $hashCodes
   */
  public function setHashCodes(array $hashCodes) {
    $this->params['hashcodes'] = $hashCodes;
  }

  /**
   * Sets file uris for searching.
   *
   * @param array $fileUris
   */
  public function setFileUris(array $fileUris) {
    $this->params['fileUris'] = $fileUris;
  }

}
