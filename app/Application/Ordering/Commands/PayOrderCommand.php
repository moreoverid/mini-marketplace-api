<?php

declare(strict_types=1);

namespace App\Application\Ordering\Commands;

use App\Domain\Ordering\ValueObjects\OrderId;

final readonly class PayOrderCommand
{
    public function __construct(
        public OrderId $orderId,
    ) {
    }
}