<?php

namespace Smartling\File\Params;

/**
 * Class DownloadMultipleFilesParameters
 *
 * @package Smartling\File\Params
 */
class DownloadMultipleFilesParameters extends DownloadFileParameters
{

    const FILE_NAME_MODE_UNCHANGED = "UNCHANGED";
    const FILE_NAME_MODE_TRIM_LEADING = "TRIM_LEADING";
    const FILE_NAME_MODE_LOCALE_LAST = "LOCALE_LAST";
    const LOCALE_MODE_LOCALE_IN_PATH = "LOCALE_IN_PATH";
    const LOCALE_MODE_LOCALE_IN_NAME = "LOCALE_IN_NAME";
    const LOCALE_MODE_LOCALE_IN_NAME_AND_PATH = "LOCALE_IN_NAME_AND_PATH";

    public function setFileUris(array $fileUris) {
        $this->set("fileUris", $fileUris);

        return $this;
    }

    public function setLocaleIds(array $localeIds) {
        $this->set("localeIds", $localeIds);

        return $this;
    }

    public function setFileNameMode($fileNameMode) {
        $allowedModes = [
            self::FILE_NAME_MODE_LOCALE_LAST,
            self::FILE_NAME_MODE_TRIM_LEADING,
            self::FILE_NAME_MODE_UNCHANGED
        ];

        if (!\in_array($fileNameMode, $allowedModes)) {
            $allowedModesString = \implode(", ", $allowedModes);

            throw new \InvalidArgumentException("File name mode '$fileNameMode' is not allowed. Allowed modes are: $allowedModesString.");
        }

        $this->set("fileNameMode", $fileNameMode);

        return $this;
    }

    public function setLocaleMode($localeMode) {
        $allowedModes = [
            self::LOCALE_MODE_LOCALE_IN_NAME,
            self::LOCALE_MODE_LOCALE_IN_PATH,
            self::LOCALE_MODE_LOCALE_IN_NAME_AND_PATH
        ];

        if (!\in_array($localeMode, $allowedModes)) {
            $allowedModesString = \implode(", ", $allowedModes);

            throw new \InvalidArgumentException("Locale mode '$localeMode' is not allowed. Allowed modes are: $allowedModesString.");
        }

        $this->set("localeMode", $localeMode);

        return $this;
    }

    public function setZipFileName($zipFileName) {
        $this->set("zipFileName", $zipFileName);

        return $this;
    }
}
