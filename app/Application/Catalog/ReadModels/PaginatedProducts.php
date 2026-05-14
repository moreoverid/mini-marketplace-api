<?php

declare(strict_types=1);

namespace App\Application\Catalog\ReadModels;

final readonly class PaginatedProducts
{
    /**
     * @param list<ProductListItem> $items
     */
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
        public int $lastPage,
    ) {
    }
}