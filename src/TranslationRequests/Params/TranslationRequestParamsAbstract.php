<?php

namespace Smartling\TranslationRequests\Params;

/**
 * Class TranslationRequestParamsAbstract
 * @package Smartling\TranslationRequests\Params
 */
class TranslationRequestParamsAbstract extends ParamsAbstract
{
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
        $this->set('contentHash', (string)$contentHash);
        return $this;
    }

    /**
     * @param $outdated
     * @return $this
     */
    public function setOutdated($outdated)
    {
        $this->set('outdated', $outdated);
        return $this;
    }

    /**
     * @param $totalWordCount
     * @return $this
     */
    public function setTotalWordCount($totalWordCount)
    {
        $this->set('totalWordCount', (int)$totalWordCount);
        return $this;
    }

    /**
     * @param $totalStringCount
     * @return $this
     */
    public function setTotalStringCount($totalStringCount)
    {
        $this->set('totalStringCount', (int)$totalStringCount);
        return $this;
    }

    /**
     * @param array $customOriginalData
     * @return $this
     */
    public function setCustomOriginalData(array $customOriginalData = [])
    {
        $this->set('customOriginalData', $customOriginalData);
        return $this;
    }
}
