<?php

namespace Smartling\Submissions\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class UpdateSubmissionParams
 * @package Smartling\Submissions\Params
 */
class UpdateSubmissionParams extends BaseParameters
{
    const DATE_TIME_FORMAT = "Y-m-d H:i:s";

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
     * @param UpdateDetailParams $detail
     * @return $this
     */
    public function addDetail(UpdateDetailParams $detail)
    {
        if (!array_key_exists('details', $this->params)) {
            $this->set('details', []);
        }

        $this->params['details'][] = $detail->exportToArray();
        return $this;
    }

}