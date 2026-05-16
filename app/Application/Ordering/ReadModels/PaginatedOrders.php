<?php

declare(strict_types=1);

namespace App\Application\Ordering\ReadModels;

final readonly class PaginatedOrders
{
    /**
     * @param list<OrderListItem> $items
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