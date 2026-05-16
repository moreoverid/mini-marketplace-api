<?php

declare(strict_types=1);

namespace App\Application\Ordering\Commands;

final readonly class CreateOrderCommand
{
    /**
     * @param list<array{product_id: string, quantity: int}> $items
     */
    public function __construct(
        public array $items,
    ) {
    }
}
