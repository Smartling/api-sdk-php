<?php

namespace Smartling\AuditLog\Params;

use InvalidArgumentException;
use Smartling\Parameters\BaseParameters;

class SearchRecordParameters extends BaseParameters
{
    const ORDER_DESC = 'desc';
    const ORDER_ASC = 'asc';

    public function __construct() {
        $this->setOffset(0);
        $this->setLimit(10);
        $this->setSort("_seq_no", SearchRecordParameters::ORDER_DESC);
    }

    public function setSearchQuery($searchQuery) {
        $this->set('q', (string) $searchQuery);

        return $this;
    }

    public function setOffset($offset) {
        if (!is_int($offset) || $offset < 0) {
            throw new InvalidArgumentException('Offset value must be grater or equal to zero.');
        }

        $this->set('offset', $offset);

        return $this;
    }

    public function setLimit($limit) {
        if (!is_int($limit) || $limit < 1) {
            throw new InvalidArgumentException('Limit value must be grater or equal to one.');
        }

        $this->set('limit', $limit);

        return $this;
    }

    public function setSort($field, $order) {
        $allowedSortOrders = [
            SearchRecordParameters::ORDER_DESC,
            SearchRecordParameters::ORDER_ASC
        ];

        if (!in_array($order, $allowedSortOrders)) {
            throw new InvalidArgumentException('Allowed sort orders are: ' . implode(', ', $allowedSortOrders));
        }

        $this->set('sort', "$field:$order");

        return $this;
    }
}
