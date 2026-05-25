<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Application\Handlers;

use App\Modules\Ordering\Application\Commands\CreateOrderCommand;
use App\Modules\Catalog\Domain\Repositories\ProductRepository;
use App\Modules\Catalog\Domain\ValueObjects\ProductId;
use App\Modules\Ordering\Domain\Entities\Order;
use App\Modules\Ordering\Domain\Repositories\OrderRepository;
use App\Modules\Ordering\Domain\ValueObjects\OrderId;
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
