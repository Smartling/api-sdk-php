<?php
namespace Smartling\File\Params;

/**
 * Class ExtendedListFilesParameters
 *
 * @package Smartling\File\Params
 */
class ExtendedListFilesParameters extends ListFilesParameters {
    /**
     * @param string $status
     *
     * @return ExtendedListFilesParameters
     */
    public function setStatus($status)
    {
        $this->set('status', $status);

        return $this;
    }
}
