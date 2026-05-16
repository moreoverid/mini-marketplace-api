<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Persistence\Eloquent;

use App\Domain\Catalog\ValueObjects\Money;
use App\Domain\Catalog\ValueObjects\ProductId;
use App\Domain\Ordering\Entities\Order;
use App\Domain\Ordering\Repositories\OrderRepository;
use App\Domain\Ordering\ValueObjects\OrderId;
use App\Domain\Ordering\ValueObjects\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentOrderRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_and_finds_order(): void
    {
        $repository = $this->app->make(OrderRepository::class);

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

        $repository->save($order);

        $foundOrder = $repository->find($order->id());

        $this->assertNotNull($foundOrder);
        $this->assertTrue($order->id()->equals($foundOrder->id()));
        $this->assertTrue($foundOrder->status()->equals(OrderStatus::pending()));
        $this->assertCount(1, $foundOrder->items());
        $this->assertSame(2000, $foundOrder->total()->amount());
        $this->assertSame('USD', $foundOrder->total()->currency());
    }

    public function test_it_saves_paid_order_status(): void
    {
        $repository = $this->app->make(OrderRepository::class);

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

        $repository->save($order);

        $foundOrder = $repository->find($order->id());

        $this->assertNotNull($foundOrder);
        $this->assertTrue($foundOrder->status()->equals(OrderStatus::paid()));
    }

    public function test_it_returns_null_when_order_does_not_exist(): void
    {
        $repository = $this->app->make(OrderRepository::class);

        $order = $repository->find(OrderId::generate());

        $this->assertNull($order);
    }
}
