<?php

namespace Smartling\Params;

class DownloadFileParameters extends BaseParameters {

  public function setRetrievalType($retrieval_type) {
    $this->params['retrievalType'] = $retrieval_type;
    return $this;
  }

  public function setIncludeOriginalStrings($include_original_strings) {
    $this->params['includeOriginalStrings'] = $include_original_strings;
    return $this;
  }
}
