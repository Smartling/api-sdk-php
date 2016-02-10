<?php

namespace Smartling\File\Params;

use Smartling\BaseApiAbstract;

/**
 * Class UploadFileParameters
 *
 * @package Smartling\Params
 */
class UploadFileParameters extends BaseParameters
{

    const CLIENT_LIB_ID_SDK = 'smartling-api-sdk-php';
    const CLIENT_LIB_ID_VERSION = '2.0.0';

    public function __construct(
        $clientLibId = BaseApiAbstract::CLIENT_LIB_ID_SDK,
        $clientLibVersion = BaseApiAbstract::CLIENT_LIB_ID_VERSION
    ) {

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
        $this->set('localeIdsToAuthorize', $locales_to_approve);

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function exportToArray()
    {
        $params = $this->params;
        $params['authorize'] = (empty($params['localeIdsToAuthorize'])) ?: false;

        return $params;
    }
}
