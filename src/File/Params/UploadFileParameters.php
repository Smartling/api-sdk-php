<?php

namespace Smartling\File\Params;

use Smartling\BaseApiAbstract;
use Smartling\Parameters\BaseParameters;

/**
 * Class UploadFileParameters
 *
 * @package Smartling\File\Params
 */
class UploadFileParameters extends BaseParameters
{

    public function __construct(
        $clientLibId = BaseApiAbstract::CLIENT_LIB_ID_SDK,
        $clientLibVersion = BaseApiAbstract::CLIENT_LIB_ID_VERSION
    ) {
        $this->setAuthorized(false);
        $this->setClientLibId($clientLibId, $clientLibVersion);
    }

    /**
     * @param string $client_lib_id
     * @param string $version
     *
     * @return UploadFileParameters
     */
    public function setClientLibId($client_lib_id, $version)
    {

        $this->set(
            'smartling.client_lib_id',
            json_encode(
                [
                    'client' => $client_lib_id,
                    'version' => $version,
                ],
                JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE
            )
        );

        return $this;
    }

    /**
     * @param string $callback_url
     *
     * @return UploadFileParameters
     */
    public function setCallbackUrl($callback_url)
    {
        $this->set('callbackUrl', $callback_url);

        return $this;
    }

    /**
     * @param int $authorized
     *
     * @return UploadFileParameters
     */
    public function setAuthorized($authorized)
    {
        $this->set('authorize', $authorized);

        return $this;
    }

    /**
     * @param array $locales_to_approve
     *
     * @return UploadFileParameters
     */
    public function setLocalesToApprove($locales_to_approve)
    {
        if (is_string($locales_to_approve)) {
            $locales_to_approve = [$locales_to_approve];
        }
        $this->set('localeIdsToAuthorize', $locales_to_approve);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function exportToArray()
    {
        $params = $this->params;

        $params['authorize'] = array_key_exists('localeIdsToAuthorize', $params)
            ? false
            : (bool)$params['authorize'];

        return $params;
    }
}
