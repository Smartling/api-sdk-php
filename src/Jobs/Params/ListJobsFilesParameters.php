<?php

namespace Smartling\Jobs\Params;

use Smartling\Parameters\BaseParameters;

class ListJobFilesParameters extends BaseParameters
{
    public function __construct($limit = null, $offset = null)
    {
        $this->setLimit($limit);
        $this->setOffset($offset);
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
}
