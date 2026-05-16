<?php

declare(strict_types=1);

namespace App\Application\Ordering\Handlers;

use App\Application\Ordering\Commands\PayOrderCommand;
use App\Application\Ordering\Exceptions\OrderNotFoundException;
use App\Application\Shared\Eventing\DomainEventDispatcher;
use App\Domain\Ordering\Entities\Order;
use App\Domain\Ordering\Repositories\OrderRepository;

final class PayOrderHandler
{
    public function __construct(
        private OrderRepository $orders,
        private DomainEventDispatcher $events,
    ) {
    }

    public function handle(PayOrderCommand $command): Order
    {
        $order = $this->orders->find($command->orderId);

        if ($order === null) {
            throw OrderNotFoundException::forId($command->orderId->value());
        }

        $order->pay();

        $this->orders->save($order);

        $this->events->dispatch(...$order->releaseEvents());

        return $order;
    }
}
