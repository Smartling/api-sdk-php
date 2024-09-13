<?php

namespace Smartling;

class Polyfills
{
    public static function array_is_list(array $array): bool
    {
        $pointer = -1;
        foreach ($array as $key => $_) {
            if ($key !== ++$pointer) {
                return false;
            }
        }
        return true;
    }
}
