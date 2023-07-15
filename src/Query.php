<?php

namespace Cabinet;

use App\Cabinet\Query\SortDirection;

class Query
{
    public function __construct(
        public ?string $search = null,
        public ?array $types = null,
        public ?array $sources = null,
        public ?int $limit = null,
        public ?int $offset = null,
    ) {
    }
}
