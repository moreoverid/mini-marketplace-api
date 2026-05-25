<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\Queries;

final readonly class SearchProductsQuery
{
    public function __construct(
        public string $query,
        public int $page,
        public int $perPage,
    ) {
    }
}
