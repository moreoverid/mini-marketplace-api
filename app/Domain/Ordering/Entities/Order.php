<?php

declare(strict_types=1);

namespace App\Domain\Ordering\Entities;

use App\Domain\Catalog\ValueObjects\Money;
use App\Domain\Catalog\ValueObjects\ProductId;
use App\Domain\Ordering\ValueObjects\OrderId;
use App\Domain\Ordering\ValueObjects\OrderStatus;
use DomainException;

final class Order
{
    /**
     * @param list<OrderItem> $items
     */
    private function __construct(
        private readonly OrderId $id,
        private OrderStatus $status,
        private array $items,
    ) {
        if ($items === []) {
            throw new DomainException('Order must contain at least one item.');
        }
    }

    /**
     * @param list<array{productId: ProductId, unitPrice: Money, quantity: int}> $items
     */
    public static function create(OrderId $id, array $items): self
    {
        $orderItems = array_map(
            static fn (array $item): OrderItem => new OrderItem(
                productId: $item['productId'],
                unitPrice: $item['unitPrice'],
                quantity: $item['quantity'],
            ),
            $items,
        );

        return new self(
            id: $id,
            status: OrderStatus::pending(),
            items: $orderItems,
        );
    }

    public function id(): OrderId
    {
        return $this->id;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    /**
     * @return list<OrderItem>
     */
    public function items(): array
    {
        return $this->items;
    }

    public function addItem(ProductId $productId, Money $unitPrice, int $quantity): void
    {
        if (! $this->status->isPending()) {
            throw new DomainException('Only pending orders can be changed.');
        }

        $this->items[] = new OrderItem(
            productId: $productId,
            unitPrice: $unitPrice,
            quantity: $quantity,
        );
    }

    public function pay(): void
    {
        if (! $this->status->isPending()) {
            throw new DomainException('Only pending orders can be paid.');
        }

        if ($this->items === []) {
            throw new DomainException('Empty order cannot be paid.');
        }

        $this->status = OrderStatus::paid();
    }

    public function total(): Money
    {
        $currency = $this->items[0]->unitPrice()->currency();
        $amount = 0;

        foreach ($this->items as $item) {
            if ($item->unitPrice()->currency() !== $currency) {
                throw new DomainException('Order items must have the same currency.');
            }

            $amount += $item->subtotal()->amount();
        }

        return new Money($amount, $currency);
    }

    public static function restore(
        OrderId $id,
        OrderStatus $status,
        array $items,
    ): self {
        return new self(
            id: $id,
            status: $status,
            items: $items,
        );
    }
}
