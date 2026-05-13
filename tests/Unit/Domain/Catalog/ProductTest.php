<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Catalog;

use App\Domain\Catalog\Entities\Product;
use App\Domain\Catalog\ValueObjects\Money;
use App\Domain\Catalog\ValueObjects\ProductId;
use DomainException;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function test_it_can_be_created(): void
    {
        $id = ProductId::generate();

        $product = new Product(
            id: $id,
            name: 'iPhone 15',
            price: new Money(99900, 'USD'),
            stock: 10,
        );

        $this->assertSame($id, $product->id());
        $this->assertSame('iPhone 15', $product->name());
        $this->assertSame(99900, $product->price()->amount());
        $this->assertSame('USD', $product->price()->currency());
        $this->assertSame(10, $product->stock());
    }

    public function test_it_cannot_be_created_with_empty_name(): void
    {
        $this->expectException(DomainException::class);

        new Product(
            id: ProductId::generate(),
            name: '',
            price: new Money(99900, 'USD'),
            stock: 10,
        );
    }

    public function test_it_cannot_be_created_with_negative_stock(): void
    {
        $this->expectException(DomainException::class);

        new Product(
            id: ProductId::generate(),
            name: 'iPhone 15',
            price: new Money(99900, 'USD'),
            stock: -1,
        );
    }

    public function test_it_can_change_price(): void
    {
        $product = new Product(
            id: ProductId::generate(),
            name: 'iPhone 15',
            price: new Money(99900, 'USD'),
            stock: 10,
        );

        $product->changePrice(new Money(109900, 'USD'));

        $this->assertSame(109900, $product->price()->amount());
    }

    public function test_it_can_increase_stock(): void
    {
        $product = new Product(
            id: ProductId::generate(),
            name: 'iPhone 15',
            price: new Money(99900, 'USD'),
            stock: 10,
        );

        $product->increaseStock(5);

        $this->assertSame(15, $product->stock());
    }

    public function test_it_can_decrease_stock(): void
    {
        $product = new Product(
            id: ProductId::generate(),
            name: 'iPhone 15',
            price: new Money(99900, 'USD'),
            stock: 10,
        );

        $product->decreaseStock(3);

        $this->assertSame(7, $product->stock());
    }

    public function test_it_cannot_decrease_stock_below_zero(): void
    {
        $product = new Product(
            id: ProductId::generate(),
            name: 'iPhone 15',
            price: new Money(99900, 'USD'),
            stock: 2,
        );

        $this->expectException(DomainException::class);

        $product->decreaseStock(3);
    }
}
