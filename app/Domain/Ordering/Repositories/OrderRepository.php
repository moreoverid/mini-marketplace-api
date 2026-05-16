<?php

declare(strict_types=1);

namespace App\Domain\Ordering\Repositories;

use App\Domain\Ordering\Entities\Order;
use App\Domain\Ordering\ValueObjects\OrderId;

interface OrderRepository
{
    public function find(OrderId $id): ?Order;

    public function save(Order $order): void;
}
