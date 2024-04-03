<?php

namespace Smartling\Batch\Params;

use JetBrains\PhpStorm\ExpectedValues;
use Smartling\Parameters\BaseParameters;

class ListBatchesParameters extends BaseParameters
{
    public function setTranslationJobUid(string $uid): self {
        $this->set('translationJobUid', $uid);

        return $this;
    }

    public function setStatus(#[ExpectedValues(['ADDING_FILES', 'COMPLETED', 'DRAFT', 'EXECUTING'])] string $status): self {
        $this->set('status', $status);

        return $this;
    }

    public function setSortBy(#[ExpectedValues(['createdDate', 'status'])] string $sortBy): self {
        $this->set('sortBy', $sortBy);

        return $this;
    }

    public function setOrderBy(#[ExpectedValues(['asc', 'desc'])] string $orderBy): self {
        $this->set('orderBy', $orderBy);

        return $this;
    }

    public function setOffset(int $offset): self {
        $this->set('offset', $offset);

        return $this;
    }

    public function setLimit(int $limit): self {
        $this->set('limit', $limit);

        return $this;
    }
}
