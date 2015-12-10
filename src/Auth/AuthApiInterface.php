<?php

namespace Smartling\Auth;

interface AuthApiInterface {

  /**
   * @return string token
   * @throws InvalidAccessTokenException
   */
  public function getAccessToken();
}
