<?php

namespace Smartling\Jobs\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class AddLocaleToJobParameters
 * @package Jobs\Params
 */
class AddLocaleToJobParameters extends BaseParameters
{

    /**
     * @param bool $syncContent
     */
    public function setSyncContent($syncContent = true) {
        $this->set('syncContent', $syncContent);
    }

}
