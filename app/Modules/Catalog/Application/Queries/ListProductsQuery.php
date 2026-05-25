<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\Queries;

final readonly class ListProductsQuery
{
    public function __construct(
        public int $page,
        public int $perPage,
        public ?string $search = null,
    ) {
    }
}
