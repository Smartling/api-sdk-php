<?php

namespace Smartling\Jobs\Params;

use Smartling\Parameters\BaseParameters;

class ListJobsParameters extends BaseParameters
{

    public function __construct($jobName = null, $limit = null, $offset = null)
    {
        $this->setName($jobName);
        $this->setLimit($limit);
        $this->setOffset($offset);
    }

    public function setName($jobName)
    {
        if (null !== $jobName) {
            if (mb_strlen($jobName, 'UTF-8') >= 170) {
                throw new \InvalidArgumentException('Job name should be less than 170 characters.');
            }
            $this->set('jobName', $jobName);
        }
    }

    public function setLimit($limit)
    {
        if (null !== $limit && 0 < (int)$limit) {
            $this->set('limit', (int)$limit);
        }
    }

    public function setOffset($offset)
    {
        if (null !== $offset && 0 < (int)$offset) {
            $this->set('offset', (int)$offset);
        }

    }

    public function setStatuses(array $statuses)
    {
        $this->set('translationJobStatus', $statuses);
    }

}
