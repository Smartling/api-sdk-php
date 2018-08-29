<?php

namespace Smartling\TranslationRequests\Params;

/**
 * Class TranslationSubmissionParamsAbstract
 * @package Smartling\TranslationRequests\Params
 */
abstract class TranslationSubmissionParamsAbstract extends ParamsAbstract
{
    /**
     * @param array $translationAssetId
     * @return $this
     */
    public function setTranslationAssetId(array $translationAssetId = [])
    {
        $this->set('translationAssetId', json_encode($translationAssetId));
        return $this;
    }

    /**
     * @param $state
     * @return $this
     */
    public function setState($state)
    {
        $state = (string)$state;
        if (in_array($state, TranslationSubmissionStates::$allowedStates, true)) {
            $this->set('state', $state);
        } else {
            throw new \UnexpectedValueException(
                'Invalid \'state\' value \'%s\', expected one of: %s',
                [
                    $state,
                    implode('|', TranslationSubmissionStates::$allowedStates)
                ]
            );
        }
        return $this;
    }

    /**
     * @param $submitterName
     * @return $this
     */
    public function setSubmitterName($submitterName)
    {
        $this->set('submitterName', (string)$submitterName);
        return $this;
    }

    /**
     * @param array $customTranslationData
     * @return $this
     */
    public function setCustomTranslationData(array $customTranslationData = [])
    {
        $this->set('customTranslationData', json_encode($customTranslationData));
        return $this;
    }

    /**
     * @param \DateTime $submittedDate
     * @return $this
     */
    public function setSubmittedDate(\DateTime $submittedDate)
    {
        $this->set('submittedDate', $submittedDate->format(static::DATE_TIME_FORMAT));
        return $this;
    }

    /**
     * @param \DateTime $lastExportDate
     * @return $this
     */
    public function setLastExportedDate(\DateTime $lastExportDate)
    {
        $this->set('lastExportedDate', $lastExportDate->format(static::DATE_TIME_FORMAT));
        return $this;
    }

    /**
     * @param $lastErrorMessage
     * @return $this
     */
    public function setLastErrorMessage($lastErrorMessage)
    {
        $this->set('lastErrorMessage', (string)$lastErrorMessage);
        return $this;
    }

    /**
     * @param $localeLastModified
     * @return $this
     */
    public function setLocaleLastModified(\DateTime $localeLastModified)
    {
        $this->set('localeLastModified', $localeLastModified->format(self::DATE_TIME_FORMAT));
        return $this;
    }

    /**
     * @param $percentComplete
     * @return $this
     */
    public function setPercentComplete($percentComplete)
    {
        $this->set('percentComplete', (int)$percentComplete);
        return $this;
    }
}
