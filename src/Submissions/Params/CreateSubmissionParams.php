<?php

namespace Smartling\Submissions\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class CreateSubmissionParams
 * @package Smartling\Submissions\Params
 */
class CreateSubmissionParams extends BaseParameters
{
    const DATE_TIME_FORMAT = "Y-m-d H:i:s";

    /**
     * @param string $submissionUid
     * @return $this
     */
    public function setSubmissionUid($submissionUid)
    {
        $this->set('submission_uid', (string)$submissionUid);
        return $this;
    }

    /**
     * @param array $originalAssetId
     * @return $this
     */
    public function setOriginalAssetId(array $originalAssetId = [])
    {
        $this->set('original_asset_id', json_encode($originalAssetId));
        return $this;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->set('title', (string)$title);
        return $this;
    }

    /**
     * @param $fileUri
     * @return $this
     */
    public function setFileUri($fileUri)
    {
        $this->set('fileUri', (string)$fileUri);
        return $this;
    }

    /**
     * @param $originalLocale
     * @return $this
     */
    public function setOriginalLocale($originalLocale)
    {
        $this->set('original_locale', (string)$originalLocale);
        return $this;
    }

    /**
     * @param $contentHash
     * @return $this
     */
    public function setContentHash($contentHash)
    {
        $this->set('content_hash', (string)$contentHash);
        return $this;
    }

    /**
     * @param $outdated
     * @return $this
     */
    public function setOutdated($outdated)
    {
        $this->set('outdated', (string)$outdated);
        return $this;
    }

    /**
     * @param $totalWordCount
     * @return $this
     */
    public function setTotalWordCount($totalWordCount)
    {
        $this->set('total_word_count', (int)$totalWordCount);
        return $this;
    }

    /**
     * @param $totalStringCount
     * @return $this
     */
    public function setTotalStringCount($totalStringCount)
    {
        $this->set('total_string_count', (int)$totalStringCount);
        return $this;
    }

    /**
     * @param \DateTime $lastModified
     * @return $this
     */
    public function setLastModified(\DateTime $lastModified)
    {
        $this->set('last_modified', $lastModified->format(self::DATE_TIME_FORMAT));
        return $this;
    }

    /**
     * @param array $customOriginalData
     * @return $this
     */
    public function setCustomOriginalData(array $customOriginalData = [])
    {
        $this->set('custom_original_data', json_encode($customOriginalData));
        return $this;
    }

    /**
     * @param CreateDetailParams $detail
     * @return $this
     */
    public function addDetail(CreateDetailParams $detail)
    {
        if (!array_key_exists('details', $this->params)) {
            $this->set('details', []);
        }

        $this->params['details'][] = $detail->exportToArray();
        return $this;
    }

}