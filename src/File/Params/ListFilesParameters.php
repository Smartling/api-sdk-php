<?php

namespace Smartling\File\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class ListFilesParameters
 *
 * @package Smartling\File\Params
 */
class ListFilesParameters extends BaseParameters
{

    /**
     * @param string $uri_mask
     *
     * @return ListFilesParameters
     */
    public function setUriMask($uri_mask)
    {
        $this->set('uriMask', $uri_mask);

        return $this;
    }

    /**
     * @param string $file_types
     *
     * @return ListFilesParameters
     */
    public function setFileTypes($file_types)
    {
        $this->set('fileTypes[]', $file_types);

        return $this;
    }

    /**
     * @param string $last_uploaded_after
     *
     * @return ListFilesParameters
     */
    public function setLastUploadedAfter($last_uploaded_after)
    {
        $this->set('lastUploadedAfter', $last_uploaded_after);

        return $this;
    }

    /**
     * @param string $last_uploaded_before
     *
     * @return ListFilesParameters
     */
    public function setLastUploadedBefore($last_uploaded_before)
    {
        $this->set('lastUploadedBefore', $last_uploaded_before);

        return $this;
    }

    /**
     * @param int $offset
     *
     * @return ListFilesParameters
     */
    public function setOffset($offset)
    {
        $this->set('offset', $offset);

        return $this;
    }

    /**
     * @param int $limit
     *
     * @return ListFilesParameters
     */
    public function setLimit($limit)
    {
        $this->set('limit', $limit);

        return $this;
    }
}
