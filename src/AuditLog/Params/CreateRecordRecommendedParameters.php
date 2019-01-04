<?php

namespace Smartling\AuditLog\Params;

use InvalidArgumentException;
use Smartling\Parameters\BaseParameters;

class CreateRecordRecommendedParameters extends BaseParameters
{
    const ACTION_TYPE_UPLOAD = 'UPLOAD';
    const ACTION_TYPE_DOWNLOAD = 'DOWNLOAD';
    const ACTION_TYPE_CANCEL = 'CANCEL';
    const ACTION_TYPE_DELETE = 'DELETE';
    const ACTION_TYPE_LOCK_FIELDS = 'LOCK_FIELDS';
    const ACTION_TYPE_UPDATE_SETTINGS = 'UPDATE_SETTINGS';

    public function __construct() {
        $this->setTime(time());
    }

    public function setBucket($bucket) {
        $this->set('bucket', (string) $bucket);

        return $this;
    }

    public function setTime($timeStamp) {
        if (!is_int($timeStamp)) {
            throw new InvalidArgumentException('Time value must be a timestamp.');
        }

        $this->set('time', date('Y-m-d\TH:i:s\Z', $timeStamp));

        return $this;
    }

    public function setActionType($actionType) {
        $allowedActionTypes = [
            CreateRecordRecommendedParameters::ACTION_TYPE_UPLOAD,
            CreateRecordRecommendedParameters::ACTION_TYPE_DOWNLOAD,
            CreateRecordRecommendedParameters::ACTION_TYPE_CANCEL,
            CreateRecordRecommendedParameters::ACTION_TYPE_DELETE,
            CreateRecordRecommendedParameters::ACTION_TYPE_LOCK_FIELDS,
            CreateRecordRecommendedParameters::ACTION_TYPE_UPDATE_SETTINGS,
        ];

        if (!in_array($actionType, $allowedActionTypes)) {
            throw new InvalidArgumentException('Allowed action types are: ' . implode(', ', $allowedActionTypes));
        }

        $this->set('action_type', $actionType);

        return $this;
    }

    public function setUserId($user_id) {
        $this->set('user_id', (string) $user_id);

        return $this;
    }

    public function setDescription($description) {
        $this->set('description', (string) $description);

        return $this;
    }

    public function setCustomField($key, $value) {
        $this->set($key, $value);

        return $this;
    }
}
