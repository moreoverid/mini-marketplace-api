<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Domain\Repositories;

use App\Modules\Ordering\Domain\Entities\Order;
use App\Modules\Ordering\Domain\ValueObjects\OrderId;

interface OrderRepository
{
    public function find(OrderId $id): ?Order;

    public function save(Order $order): void;
}
