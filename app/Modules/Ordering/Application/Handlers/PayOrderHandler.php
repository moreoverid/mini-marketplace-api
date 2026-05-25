<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Application\Handlers;

use App\Modules\Ordering\Application\Commands\PayOrderCommand;
use App\Modules\Ordering\Application\Exceptions\OrderNotFoundException;
use App\Modules\Shared\Application\Eventing\DomainEventDispatcher;
use App\Modules\Ordering\Domain\Entities\Order;
use App\Modules\Ordering\Domain\Repositories\OrderRepository;

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
