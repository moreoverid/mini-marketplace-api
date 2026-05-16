<?php

declare(strict_types=1);

namespace App\Application\Ordering\Handlers;

use App\Application\Ordering\Commands\CreateOrderCommand;
use App\Domain\Catalog\Repositories\ProductRepository;
use App\Domain\Catalog\ValueObjects\ProductId;
use App\Domain\Ordering\Entities\Order;
use App\Domain\Ordering\Repositories\OrderRepository;
use App\Domain\Ordering\ValueObjects\OrderId;
use DomainException;

final class CreateOrderHandler
{
    public function __construct(
        private ProductRepository $products,
        private OrderRepository $orders,
    ) {
    }

    public function handle(CreateOrderCommand $command): Order
    {
        $items = [];

        foreach ($command->items as $item) {
            $productId = ProductId::fromString($item['product_id']);

            $product = $this->products->find($productId);

            if ($product === null) {
                throw new DomainException('Product not found.');
            }

            $items[] = [
                'productId' => $product->id(),
                'unitPrice' => $product->price(),
                'quantity' => $item['quantity'],
            ];
        }

        $order = Order::create(
            id: OrderId::generate(),
            items: $items,
        );

        $this->orders->save($order);

        return $order;
    }
}
