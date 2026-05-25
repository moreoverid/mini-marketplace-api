<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Ordering;

use App\Modules\Ordering\Application\Commands\PayOrderCommand;
use App\Modules\Ordering\Application\Exceptions\OrderNotFoundException;
use App\Modules\Ordering\Application\Handlers\PayOrderHandler;
use App\Modules\Shared\Application\Eventing\DomainEventDispatcher;
use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Catalog\Domain\ValueObjects\ProductId;
use App\Modules\Ordering\Domain\Entities\Order;
use App\Modules\Ordering\Domain\Events\OrderPaid;
use App\Modules\Ordering\Domain\Repositories\OrderRepository;
use App\Modules\Ordering\Domain\ValueObjects\OrderId;
use App\Modules\Ordering\Domain\ValueObjects\OrderStatus;
use App\Modules\Shared\Domain\Events\DomainEvent;
use DomainException;
use PHPUnit\Framework\TestCase;

final class PayOrderHandlerTest extends TestCase
{
    public function test_it_pays_order(): void
    {
        $orders = new PayOrderInMemoryOrderRepository();
        $events = new SpyDomainEventDispatcher();

        $order = Order::create(
            id: OrderId::generate(),
            items: [
                [
                    'productId' => ProductId::generate(),
                    'unitPrice' => new Money(1000, 'USD'),
                    'quantity' => 2,
                ],
            ],
        );

        $orders->save($order);

        $handler = new PayOrderHandler($orders, $events);

        $paidOrder = $handler->handle(new PayOrderCommand($order->id()));

        $savedOrder = $orders->find($order->id());

        $this->assertNotNull($savedOrder);
        $this->assertTrue($paidOrder->status()->equals(OrderStatus::paid()));
        $this->assertTrue($savedOrder->status()->equals(OrderStatus::paid()));

        $this->assertCount(1, $events->events);
        $this->assertInstanceOf(OrderPaid::class, $events->events[0]);
        $this->assertTrue($order->id()->equals($events->events[0]->orderId()));
    }

    public function test_it_fails_when_order_does_not_exist(): void
    {
        $orders = new PayOrderInMemoryOrderRepository();
        $events = new SpyDomainEventDispatcher();

        $handler = new PayOrderHandler($orders, $events);

        $this->expectException(OrderNotFoundException::class);

        $handler->handle(new PayOrderCommand(OrderId::generate()));
    }

    public function test_it_cannot_pay_order_twice(): void
    {
        $orders = new PayOrderInMemoryOrderRepository();
        $events = new SpyDomainEventDispatcher();

        $order = Order::create(
            id: OrderId::generate(),
            items: [
                [
                    'productId' => ProductId::generate(),
                    'unitPrice' => new Money(1000, 'USD'),
                    'quantity' => 2,
                ],
            ],
        );

        $order->pay();
        $order->releaseEvents();

        $orders->save($order);

        $handler = new PayOrderHandler($orders, $events);

        $this->expectException(DomainException::class);

        $handler->handle(new PayOrderCommand($order->id()));
    }
}

final class PayOrderInMemoryOrderRepository implements OrderRepository
{
    /**
     * @var array<string, Order>
     */
    private array $orders = [];

    public function find(OrderId $id): ?Order
    {
        return $this->orders[$id->value()] ?? null;
    }

    public function save(Order $order): void
    {
        $this->orders[$order->id()->value()] = $order;
    }
}

final class SpyDomainEventDispatcher implements DomainEventDispatcher
{
    /**
     * @var list<DomainEvent>
     */
    public array $events = [];

    public function dispatch(DomainEvent ...$events): void
    {
        array_push($this->events, ...$events);
    }
}
