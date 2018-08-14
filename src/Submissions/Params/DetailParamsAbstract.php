<?php

namespace Smartling\Submissions\Params;

/**
 * Class DetailParamsAbstract
 * @package Smartling\Submissions\Params
 */
abstract class DetailParamsAbstract extends ParamsAbstract
{
    /**
     * @param array $translationAssetId
     * @return $this
     */
    public function setTranslationAssetId(array $translationAssetId = [])
    {
        $this->set('translation_asset_id', json_encode($translationAssetId));
        return $this;
    }

    /**
     * @param $state
     * @return $this
     */
    public function setState($state)
    {
        $state = (string)$state;
        if (in_array($state, SubmissionDetailsStates::$allowedStates, true)) {
            $this->set('state', $state);
        }
        return $this;
    }

    /**
     * @param $submitter
     * @return $this
     */
    public function setSubmitter($submitter)
    {
        $this->set('submitter', (string)$submitter);
        return $this;
    }

    /**
     * @param array $customTranslationData
     * @return $this
     */
    public function setCustomTranslationData(array $customTranslationData = [])
    {
        $this->set('custom_translation_data', json_encode($customTranslationData));
        return $this;
    }

    /**
     * @param $authorizedStringCount
     * @return $this
     */
    public function setAuthorizedStringCount($authorizedStringCount)
    {
        $this->set('authorized_string_count', (int)$authorizedStringCount);
        return $this;
    }

    /**
     * @param $completedStringCount
     * @return $this
     */
    public function setCompletedStringCount($completedStringCount)
    {
        $this->set('completed_string_count', (int)$completedStringCount);
        return $this;
    }

    /**
     * @param \DateTime $submitted
     * @return $this
     */
    public function setSubmitted(\DateTime $submitted)
    {
        $this->set('submitted', $submitted->format(self::DATE_TIME_FORMAT));
        return $this;
    }

    /**
     * @param \DateTime $applied
     * @return $this
     */
    public function setApplied(\DateTime $applied)
    {
        $this->set('applied', $applied->format(self::DATE_TIME_FORMAT));
        return $this;
    }

    /**
     * @param $lastError
     * @return $this
     */
    public function setLastError($lastError)
    {
        $this->set('last_error', (string)$lastError);
        return $this;
    }
}
