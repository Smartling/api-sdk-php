<?php

namespace Smartling\AuditLog\Params;

use InvalidArgumentException;
use Smartling\Parameters\BaseParameters;

class CreateRecordParameters extends BaseParameters
{
    const ACTION_TYPE_UPLOAD = 'UPLOAD';
    const ACTION_TYPE_DOWNLOAD = 'DOWNLOAD';
    const ACTION_TYPE_CANCEL = 'CANCEL';
    const ACTION_TYPE_DELETE = 'DELETE';
    const ACTION_TYPE_LOCK_FIELDS = 'LOCK_FIELDS';
    const ACTION_TYPE_UPDATE_SETTINGS = 'UPDATE_SETTINGS';

    public function __construct() {
        $this->setActionTime(time());
    }

    public function setActionTime($timeStamp) {
        if (!is_int($timeStamp)) {
            throw new InvalidArgumentException('Time value must be a timestamp.');
        }

        $this->set('actionTime', date('Y-m-d\TH:i:s\Z', $timeStamp));

        return $this;
    }

    public function setActionType($actionType) {
        $allowedActionTypes = [
            CreateRecordParameters::ACTION_TYPE_UPLOAD,
            CreateRecordParameters::ACTION_TYPE_DOWNLOAD,
            CreateRecordParameters::ACTION_TYPE_CANCEL,
            CreateRecordParameters::ACTION_TYPE_DELETE,
            CreateRecordParameters::ACTION_TYPE_LOCK_FIELDS,
            CreateRecordParameters::ACTION_TYPE_UPDATE_SETTINGS,
        ];

        if (!in_array($actionType, $allowedActionTypes)) {
            throw new InvalidArgumentException('Allowed action types are: ' . implode(', ', $allowedActionTypes));
        }

        $this->set('actionType', $actionType);

        return $this;
    }

    public function setFileUri($fileUri) {
        $this->set('fileUri', (string) $fileUri);

        return $this;
    }

    public function setFileUid($fileUid) {
        $this->set('fileUid', (string) $fileUid);

        return $this;
    }

    public function setSourceLocaleId($sourceLocaleId) {
        $this->set('sourceLocaleId', (string) $sourceLocaleId);

        return $this;
    }

    public function setTargetLocaleIds(array $targetLocaleIds) {
        $this->set('targetLocaleIds', $targetLocaleIds);

        return $this;
    }

    public function setTranslationJobUid($translationJobUid) {
        $this->set('translationJobUid', (string) $translationJobUid);

        return $this;
    }

    public function setTranslationJobName($translationJobName) {
        $this->set('translationJobName', (string) $translationJobName);

        return $this;
    }

    public function setTranslationJobDueDate($translationJobDueDate) {
        $this->set('translationJobDueDate', (string) $translationJobDueDate);

        return $this;
    }

    public function setTranslationJobAuthorize($translationJobAuthorize) {
        $this->set('translationJobAuthorize', (bool) $translationJobAuthorize);

        return $this;
    }

    public function setBatchUid($batchUid) {
        $this->set('batchUid', (string) $batchUid);

        return $this;
    }

    public function setDescription($description) {
        $this->set('description', (string) $description);

        return $this;
    }

    public function setClientUserId($clientUserId) {
        $this->set('clientUserId', (string) $clientUserId);

        return $this;
    }

    public function setClientUserEmail($clientUserEmail) {
        $this->set('clientUserEmail', (string) $clientUserEmail);

        return $this;
    }

    public function setClientUserName($clientUserName) {
        $this->set('clientUserName', (string) $clientUserName);

        return $this;
    }

    public function setEnvId($envId) {
        $this->set('envId', (string) $envId);

        return $this;
    }

    public function setClientData($key, $value) {
        if (empty($this->params['clientData'])) {
            $this->params['clientData'] = [];
        }

        $this->params['clientData'] += [$key => $value];

        return $this;
    }
}
