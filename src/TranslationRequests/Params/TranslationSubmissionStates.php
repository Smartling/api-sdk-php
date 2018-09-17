<?php

namespace Smartling\TranslationRequests\Params;

/**
 * Class TranslationSubmissionStates
 * @package Smartling\TranslationRequests\Params
 */
class TranslationSubmissionStates
{
    const STATE_NEW = 'New';
    const STATE_IN_PROGRESS = 'In Progress';
    const STATE_TRANSLATED = 'Translated';
    const STATE_CHANGED = 'Changed';
    const STATE_FAILED = 'Failed';
    const STATE_DELETED = 'Deleted';

    /**
     * @var array
     */
    public static $allowedStates = [
        self::STATE_NEW,
        self::STATE_IN_PROGRESS,
        self::STATE_TRANSLATED,
        self::STATE_CHANGED,
        self::STATE_FAILED,
        self::STATE_DELETED
    ];
}
