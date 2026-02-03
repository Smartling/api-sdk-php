<?php

namespace Smartling\FileTranslations\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class TranslateFileParameters
 *
 * Parameters for initiating file translation via machine translation.
 *
 * @package Smartling\FileTranslations\Params
 */
class TranslateFileParameters extends BaseParameters
{
    /**
     * Set source locale ID.
     *
     * @param string $sourceLocaleId
     *   Source language code (e.g., 'en', 'en-US')
     *
     * @return TranslateFileParameters
     */
    public function setSourceLocaleId($sourceLocaleId)
    {
        $this->set('sourceLocaleId', $sourceLocaleId);

        return $this;
    }

    /**
     * Set target locale IDs.
     *
     * @param array $targetLocaleIds
     *   Array of target language codes (e.g., ['es', 'fr', 'de'])
     *
     * @return TranslateFileParameters
     */
    public function setTargetLocaleIds(array $targetLocaleIds)
    {
        $this->set('targetLocaleIds', $targetLocaleIds);

        return $this;
    }

    /**
     * Set callback URL for completion notification.
     *
     * @param string $callbackUrl
     *   Webhook URL to be called when translation completes
     *
     * @return TranslateFileParameters
     */
    public function setCallbackUrl($callbackUrl)
    {
        $this->set('callbackUrl', $callbackUrl);

        return $this;
    }
}
