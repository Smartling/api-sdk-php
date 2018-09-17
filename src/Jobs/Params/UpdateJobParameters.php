<?php

namespace Smartling\Jobs\Params;

use Smartling\Parameters\BaseParameters;

/**
 * Class UpdateJobParameters
 * @package Jobs\Params
 */
class UpdateJobParameters extends BaseParameters
{

    public function setName($jobName)
    {
        if (mb_strlen($jobName, 'UTF-8') >= 170) {
            throw new \InvalidArgumentException('Job name should be less than 170 characters.');
        }
        $this->set('jobName', $jobName);
    }

    public function setDescription($description)
    {
        if (mb_strlen($description, 'UTF-8') >= 2000) {
            throw new \InvalidArgumentException('Job description should be less than 2000 characters.');
        }
        $this->set('description', $description);
    }

    public function setDueDate(\DateTime $dueDate)
    {
        if ($dueDate->getTimestamp() < time()) {
            throw new \InvalidArgumentException('Job Due Date cannot be in the past.');
        }
        $this->set('dueDate', $dueDate->format('Y-m-d\TH:i:s\Z'));
    }

}
