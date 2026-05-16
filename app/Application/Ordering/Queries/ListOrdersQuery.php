<?php

declare(strict_types=1);

namespace App\Application\Ordering\Queries;

final readonly class ListOrdersQuery
{
    public function __construct(
        public int $page,
        public int $perPage,
        public ?string $status = null,
    ) {
    }
}
