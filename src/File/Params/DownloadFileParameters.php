<?php

namespace Smartling\File\Params;

use Smartling\Exceptions\SmartlingApiException;
use Smartling\Parameters\BaseParameters;

/**
 * Class DownloadFileParameters
 *
 * @package Smartling\File\Params
 */
class DownloadFileParameters extends BaseParameters
{

    const RETRIEVAL_TYPE_PUBLISHED = 'published';

    const RETRIEVAL_TYPE_PENDING = 'pending';

    const RETRIEVAL_TYPE_PSEUDO = 'pseudo';

    /**
     * @param string $retrievalType
     *
     * @return DownloadFileParameters
     * @throws SmartlingApiException
     */
    public function setRetrievalType($retrievalType)
    {

        $validRetrivalType = in_array(
            $retrievalType,
            [
                self::RETRIEVAL_TYPE_PENDING,
                self::RETRIEVAL_TYPE_PUBLISHED,
                self::RETRIEVAL_TYPE_PSEUDO,
            ],
            true
        );

        if (!$validRetrivalType) {
            throw new SmartlingApiException('Unknown retrieval type: ' . var_export($retrievalType, true));
        }

        $this->set('retrievalType', $retrievalType);

        return $this;
    }

    public function setIncludeOriginalStrings($include_original_strings)
    {
        $this->params['includeOriginalStrings'] = $include_original_strings;

        return $this;
    }
}
