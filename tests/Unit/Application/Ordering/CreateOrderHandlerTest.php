<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Ordering;

use App\Application\Ordering\Commands\CreateOrderCommand;
use App\Application\Ordering\Handlers\CreateOrderHandler;
use App\Domain\Catalog\Entities\Product;
use App\Domain\Catalog\Repositories\ProductRepository;
use App\Domain\Catalog\ValueObjects\Money;
use App\Domain\Catalog\ValueObjects\ProductId;
use App\Domain\Ordering\Entities\Order;
use App\Domain\Ordering\Repositories\OrderRepository;
use App\Domain\Ordering\ValueObjects\OrderId;
use App\Domain\Ordering\ValueObjects\OrderStatus;
use DomainException;
use PHPUnit\Framework\TestCase;

final class CreateOrderHandlerTest extends TestCase
{
    public function test_it_creates_order_from_products(): void
    {
        $products = new InMemoryProductRepository();
        $orders = new InMemoryOrderRepository();

        $productId = ProductId::generate();

        $products->save(new Product(
            id: $productId,
            name: 'iPhone 15',
            price: new Money(99900, 'USD'),
            stock: 10,
        ));

        $handler = new CreateOrderHandler($products, $orders);

        $order = $handler->handle(new CreateOrderCommand(
            items: [
                [
                    'product_id' => $productId->value(),
                    'quantity' => 2,
                ],
            ],
        ));

        $savedOrder = $orders->find($order->id());

        $this->assertNotNull($savedOrder);
        $this->assertTrue($order->id()->equals($savedOrder->id()));
        $this->assertTrue($savedOrder->status()->equals(OrderStatus::pending()));
        $this->assertCount(1, $savedOrder->items());
        $this->assertSame(199800, $savedOrder->total()->amount());
        $this->assertSame('USD', $savedOrder->total()->currency());
    }

    public function test_it_fails_when_product_does_not_exist(): void
    {
        $products = new InMemoryProductRepository();
        $orders = new InMemoryOrderRepository();

        $handler = new CreateOrderHandler($products, $orders);

        $this->expectException(DomainException::class);

        $handler->handle(new CreateOrderCommand(
            items: [
                [
                    'product_id' => ProductId::generate()->value(),
                    'quantity' => 1,
                ],
            ],
        ));
    }

    public function test_it_fails_when_quantity_is_zero(): void
    {
        $products = new InMemoryProductRepository();
        $orders = new InMemoryOrderRepository();

        $productId = ProductId::generate();

        $products->save(new Product(
            id: $productId,
            name: 'iPhone 15',
            price: new Money(99900, 'USD'),
            stock: 10,
        ));

        $handler = new CreateOrderHandler($products, $orders);

        $this->expectException(DomainException::class);

        $handler->handle(new CreateOrderCommand(
            items: [
                [
                    'product_id' => $productId->value(),
                    'quantity' => 0,
                ],
            ],
        ));
    }
}

final class InMemoryProductRepository implements ProductRepository
{
    /**
     * @var array<string, Product>
     */
    private array $products = [];

    public function find(ProductId $id): ?Product
    {
        return $this->products[$id->value()] ?? null;
    }

    public function save(Product $product): void
    {
        $this->products[$product->id()->value()] = $product;
    }
}

final class InMemoryOrderRepository implements OrderRepository
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
