<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Application\Commands;

use App\Modules\Ordering\Domain\ValueObjects\OrderId;

final readonly class PayOrderCommand
{
    public function __construct(
        public OrderId $orderId,
    ) {
    }
}