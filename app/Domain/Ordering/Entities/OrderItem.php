<?php

declare(strict_types=1);

namespace App\Domain\Ordering\Entities;

use App\Domain\Catalog\ValueObjects\Money;
use App\Domain\Catalog\ValueObjects\ProductId;
use DomainException;

final readonly class OrderItem
{
    public function __construct(
        private ProductId $productId,
        private Money $unitPrice,
        private int $quantity,
    ) {
        if ($quantity <= 0) {
            throw new DomainException('Order item quantity must be greater than zero.');
        }
    }

    public function productId(): ProductId
    {
        return $this->productId;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function subtotal(): Money
    {
        return new Money(
            amount: $this->unitPrice->amount() * $this->quantity,
            currency: $this->unitPrice->currency(),
        );
    }
}
