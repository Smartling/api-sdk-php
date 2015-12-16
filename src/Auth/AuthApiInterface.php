<?php

namespace Smartling\Auth;

interface AuthApiInterface {

  /**
   * @return string token
   * @throws Smartling\Exceptions\InvalidAccessTokenException
   */
  public function getAccessToken();

  public function getTokenType();

  public function resetToken();
}
