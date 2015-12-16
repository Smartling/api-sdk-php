<?php

namespace Smartling\Params;

use Smartling\Exceptions\SmartlingApiException;

class DownloadFileParameters extends BaseParameters {
  const RETRIEVAL_TYPE_PUBLISHED = 'published';
  const RETRIEVAL_TYPE_PENDING = 'pending';
  const RETRIEVAL_TYPE_PSEUDO = 'pseudo';

  public function setRetrievalType($retrieval_type) {
    if (!in_array($retrieval_type, [self::RETRIEVAL_TYPE_PENDING,
      self::RETRIEVAL_TYPE_PUBLISHED, self::RETRIEVAL_TYPE_PSEUDO])) {
      throw new SmartlingApiException('Unknown retrieval type: ' . $retrieval_type);
    }

    $this->params['retrievalType'] = $retrieval_type;
    return $this;
  }

  public function setIncludeOriginalStrings($include_original_strings) {
    $this->params['includeOriginalStrings'] = $include_original_strings;
    return $this;
  }
}
