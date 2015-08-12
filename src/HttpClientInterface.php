<?php

namespace Smartling\Api;

interface HttpClientInterface {

  const REQUEST_TYPE_GET = 'GET';
  const REQUEST_TYPE_POST = 'POST';
  const REQUEST_TYPE_PUT = 'PUT';
  const REQUEST_TYPE_DELETE = 'DELETE';

  /**
   * @param string $method
   * @return self
   */
  public function setMethod($method);

  /**
   * @return string
   */
  public function getStatus();

  /**
   * @param string $uri
   * @return self
   */
  public function setUri($uri);

  /**
   * @param array $data
   * @return string
   */
  public function request(array $data);

  /**
   * @return string
   */
  public function getContent();

  /**
   * @param boolean $flag
   * @return self
   */
  public function requireUploadFile($flag);

  /**
   * @param boolean $flag
   * @return self
   */
  public function requireUploadContent($flag);

  /**
   * @return string
   */
  public function getErrorMessage();

}
