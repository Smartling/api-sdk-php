<?php

namespace Smartling\Logger;

use Psr\Log\LoggerInterface;

class DevNullLogger implements LoggerInterface {

  /**
   * {@inheritdoc}
   */
  public function emergency($message, array $context = array(), $ignore_settings = FALSE) {
    return $this->log(WATCHDOG_EMERGENCY, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function alert($message, array $context = array(), $ignore_settings = FALSE) {
    return $this->log(WATCHDOG_ALERT, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function critical($message, array $context = array(), $ignore_settings = FALSE) {
    return $this->log(WATCHDOG_CRITICAL, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function error($message, array $context = array(), $ignore_settings = FALSE) {
    return $this->log(WATCHDOG_ERROR, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function warning($message, array $context = array(), $ignore_settings = FALSE) {
    return $this->log(WATCHDOG_WARNING, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function notice($message, array $context = array(), $ignore_settings = FALSE) {
    return $this->log(WATCHDOG_NOTICE, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function info($message, array $context = array(), $ignore_settings = FALSE) {
    return $this->log(WATCHDOG_INFO, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function debug($message, array $context = array(), $ignore_settings = FALSE) {
    return $this->log(WATCHDOG_DEBUG, $message, $context);
  }

  /**
   * {@inheritdoc}
   */
  public function log($level, $message, array $context = array(), $ignore_settings = FALSE) {
    return TRUE;
  }
}