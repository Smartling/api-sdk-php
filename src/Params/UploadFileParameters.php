<?php

namespace Smartling\Params;

class UploadFileParameters extends BaseParameters {
  public function __construct() {
    $this->params['smartling.client_lib_id'] = "{\"client\":\"api-sdk-php\",\"version\":\"2.x\"}";
    return $this;
  }

  public function setClientLibId($client_lib_id, $version) {
    $json = ['client' => $client_lib_id, 'version' => $version];
    $this->params['smartling.client_lib_id'] = json_encode($json);
    return $this;
  }

  public function setCallbackUrl($callback_url) {
    $this->params['callbackUrl'] = $callback_url;
    return $this;
  }

  public function setAuthorized($authorized) {
    //@todo: accroding to the doc this will be renamed to "authorize" with default value FALSE
    $this->params['approved'] = (int) $authorized;
    return $this;
  }

  public function setLocalesToApprove($locales_to_approve) {
    $this->params['localesToApprove'] = $locales_to_approve;
    return $this;
  }
}
