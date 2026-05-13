<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Entities;

use App\Domain\Catalog\ValueObjects\Money;
use App\Domain\Catalog\ValueObjects\ProductId;
use DomainException;

final class Product
{
    private string $name;

    private Money $price;

    private int $stock;

    public function __construct(
        private readonly ProductId $id,
        string $name,
        Money $price,
        int $stock,
    ) {
        $name = trim($name);

        if ($name === '') {
            throw new DomainException('Product name cannot be empty.');
        }

        if ($stock < 0) {
            throw new DomainException('Product stock cannot be negative.');
        }

        $this->name = $name;
        $this->price = $price;
        $this->stock = $stock;
    }

    public function id(): ProductId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function price(): Money
    {
        return $this->price;
    }

    public function stock(): int
    {
        return $this->stock;
    }

    public function rename(string $name): void
    {
        $name = trim($name);

        if ($name === '') {
            throw new DomainException('Product name cannot be empty.');
        }

        $this->name = $name;
    }

    public function changePrice(Money $price): void
    {
        $this->price = $price;
    }

    public function increaseStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new DomainException('Quantity must be greater than zero.');
        }

        $this->stock += $quantity;
    }

    public function decreaseStock(int $quantity): void
    {
        if ($quantity <= 0) {
            throw new DomainException('Quantity must be greater than zero.');
        }

        if ($quantity > $this->stock) {
            throw new DomainException('Not enough product stock.');
        }

        $this->stock -= $quantity;
    }
}
