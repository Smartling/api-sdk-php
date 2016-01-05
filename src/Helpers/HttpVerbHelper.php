<?php

namespace Smartling\Helpers;

/**
 * Class HttpVerbHelper
 *
 * @package Smartling\Helpers
 */
class HttpVerbHelper
{

    const HTTP_VERB_GET = 'get';

    const HTTP_VERB_POST = 'post';

    const HTTP_VERB_DELETE = 'delete';

    public static $verbs = [
        self::HTTP_VERB_GET,
        self::HTTP_VERB_POST,
        self::HTTP_VERB_DELETE
    ];
}