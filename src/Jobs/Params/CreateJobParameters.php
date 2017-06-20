<?php

namespace Smartling\Jobs\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class CreateJobParameters
 * @package Jobs\Params
 */
class CreateJobParameters extends BaseParameters
{

    public function setJobName($jobName)
    {
        if (mb_strlen($jobName, 'UTF-8') >= 170) {
            throw new \InvalidArgumentException('Job name should be less than 170 characters.');
        }
        $this->params['jobName'] = $jobName;
    }

    public function setDescription($description)
    {
        if (mb_strlen($description, 'UTF-8') >= 2000) {
            throw new \InvalidArgumentException('Job description should be less than 2000 characters.');
        }
        $this->params['description'] = $description;
    }

    public function setDueDate(\DateTime $dueDate)
    {
        if ($dueDate->getTimestamp() < time()) {
            throw new \InvalidArgumentException('Job Due Date cannot be in the past.');
        }
        $this->params['dueDate'] = $dueDate->format('Y-m-d\TH:i:s\Z');
    }

    public function setTargetLocales(array $targetLocales = []) {
        $this->params['targetLocaleIds'] = $targetLocales;
    }
}