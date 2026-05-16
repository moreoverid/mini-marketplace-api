<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Ordering;

use App\Domain\Catalog\ValueObjects\Money;
use App\Domain\Catalog\ValueObjects\ProductId;
use App\Domain\Ordering\Entities\Order;
use App\Domain\Ordering\ValueObjects\OrderId;
use App\Domain\Ordering\ValueObjects\OrderStatus;
use DomainException;
use PHPUnit\Framework\TestCase;

final class OrderTest extends TestCase
{
    public function test_it_can_be_created_with_items(): void
    {
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

        $this->assertTrue($order->status()->equals(OrderStatus::pending()));
        $this->assertCount(1, $order->items());
        $this->assertSame(2000, $order->total()->amount());
        $this->assertSame('USD', $order->total()->currency());
    }

    public function test_it_cannot_be_created_without_items(): void
    {
        $this->expectException(DomainException::class);

        Order::create(
            id: OrderId::generate(),
            items: [],
        );
    }

    public function test_it_cannot_be_created_with_zero_quantity(): void
    {
        $this->expectException(DomainException::class);

        Order::create(
            id: OrderId::generate(),
            items: [
                [
                    'productId' => ProductId::generate(),
                    'unitPrice' => new Money(1000, 'USD'),
                    'quantity' => 0,
                ],
            ],
        );
    }

    public function test_it_can_be_paid(): void
    {
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

        $this->assertTrue($order->status()->equals(OrderStatus::paid()));
    }

    public function test_it_cannot_be_paid_twice(): void
    {
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

        $this->expectException(DomainException::class);

        $order->pay();
    }

    public function test_it_cannot_add_item_after_payment(): void
    {
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

        $this->expectException(DomainException::class);

        $order->addItem(
            productId: ProductId::generate(),
            unitPrice: new Money(500, 'USD'),
            quantity: 1,
        );
    }
}
