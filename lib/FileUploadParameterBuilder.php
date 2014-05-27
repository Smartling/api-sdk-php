<?php

class FileUploadParameterBuilder {

  /**
   * api file type
   *
   * @var string
   */
  protected $_fileType = "";

  /**
   * api file uri
   *
   * @var string
   */
  protected $_fileUri = "";

  /**
   * api callback url
   *
   * @var string
   */
  protected $_callbackUrl = "";

  /**
   * api approved
   *
   * @var bool
   */
  protected $_approved = TRUE;

  /**
   * api locales to approve
   *
   * @var array
   */
  protected $_localesToApprove = array();

  /**
   * api overwrite approved locales
   *
   * @var bool
   */
  protected $_overwriteApprovedLocales = FALSE;

  /**
   * api parameters array
   *
   * @var array
   */
  public $_parametersArray = array();

  public function __construct() {
    $this->_parametersArray = array(
      'fileType' => $this->_fileType,
      'fileUri' => $this->_fileUri,
      'callbackUrl' => $this->_callbackUrl,
      'approved' => $this->_approved,
      'overwriteApprovedLocales' => $this->_overwriteApprovedLocales,
    );
  }

  /**
   * set parameter approved
   *
   * @param bool $approved
   */
  public function setApproved($approved = TRUE) {
    $this->_approved = $approved;
    $this->_parametersArray['approved'] = $approved;
    return $this;
  }

  /**
   * set parameter fileType
   *
   * @param string $fileType
   */
  public function setFileType($fileType) {
    $this->_fileType = $fileType;
    $this->_parametersArray['fileType'] = $fileType;
    return $this;
  }

  /**
   * set parameter fileUri
   *
   * @param string $fileUri
   */
  public function setFileUri($fileUri) {
    $this->_fileUri = $fileUri;
    $this->_parametersArray['fileUri'] = $fileUri;
    return $this;
  }

  /**
   * set parameter callbackUrl
   *
   * @param string $callbackUrl
   */
  public function setCallbackUrl($callbackUrl) {
    $this->_callbackUrl = $callbackUrl;
    $this->_parametersArray['callbackUrl'] = $callbackUrl;
    return $this;
  }

  /**
   * set parameter overwriteApprovedLocales
   *
   * @param bool $overwriteApprovedLocales
   */
  public function setOverwriteApprovedLocales($overwriteApprovedLocales = 0) {
    $this->_overwriteApprovedLocales = (int)$overwriteApprovedLocales;
    $this->_parametersArray['overwriteApprovedLocales'] = (int)$overwriteApprovedLocales;
    return $this;
  }

  /**
   * set parameter localesToApprove
   *
   * @param array $localesToApprove
   */
  public function setLocalesToApprove($localesToApprove) {
    if (is_array($localesToApprove)) {
      $this->_localesToApprove = array_unique($localesToApprove);
      $i = 0;
      foreach ($localesToApprove as $locale_code) {
        $this->_parametersArray['localesToApprove[' . $i . ']'] = $locale_code;
        $i++;
      }
    }
    return $this;
  }

  /**
   * return all parameters
   *
   * @return array
   */
  public function buildParameters() {
    $params = array();
    foreach ($this->_parametersArray as $key => $value) {
      $params[$key] = $value;
    }
    return $params;
  }

}
