<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Application\ReadModels;

final readonly class OrderListItem
{
    public function __construct(
        public string $id,
        public string $status,
        public int $totalAmount,
        public string $currency,
        public int $itemsCount,
        public ?string $createdAt,
    ) {
    }
}
