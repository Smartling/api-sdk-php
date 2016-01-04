<?php

namespace Smartling\AuthApi;

use Smartling\Exceptions\InvalidAccessTokenException;

/**
 * Interface AuthApiInterface
 *
 * @package Smartling\Auth
 */
interface AuthApiInterface
{

    /**
     * @return string token
     * @throws InvalidAccessTokenException
     */
    public function getAccessToken();

    /**
     * @return string
     */
    public function getTokenType();

    /**
     * @return void
     */
    public function resetToken();
}
