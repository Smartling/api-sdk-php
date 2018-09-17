<?php

namespace Smartling\Jobs\Params;

/**
 * Class CreateJobParameters
 * @package Jobs\Params
 */
class CreateJobParameters extends UpdateJobParameters
{

    /**
     * @param array $targetLocales
     */
    public function setTargetLocales(array $targetLocales = []) {
        $this->set('targetLocaleIds', $targetLocales);
    }

}
