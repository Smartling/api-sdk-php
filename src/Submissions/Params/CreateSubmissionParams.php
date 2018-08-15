<?php

namespace Smartling\Submissions\Params;

/**
 * Class CreateSubmissionParams
 * @package Smartling\Submissions\Params
 */
class CreateSubmissionParams extends SubmissionParamsAbstract
{
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
