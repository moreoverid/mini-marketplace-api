<?php

declare(strict_types=1);

namespace App\Application\Catalog\Queries;

final readonly class ListProductsQuery
{
    public function __construct(
        public int $page,
        public int $perPage,
        public ?string $search = null,
    ) {
    }
}
