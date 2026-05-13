<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Catalog;

use App\Application\Catalog\Commands\CreateProductCommand;
use App\Application\Catalog\Handlers\CreateProductHandler;
use App\Domain\Catalog\Entities\Product;
use App\Domain\Catalog\Repositories\ProductRepository;
use App\Domain\Catalog\ValueObjects\ProductId;
use DomainException;
use PHPUnit\Framework\TestCase;

final class CreateProductHandlerTest extends TestCase
{
    public function test_it_creates_product(): void
    {
        $repository = new InMemoryProductRepository();

        $handler = new CreateProductHandler($repository);

        $productId = $handler->handle(new CreateProductCommand(
            name: 'iPhone 15',
            priceAmount: 99900,
            currency: 'USD',
            stock: 10,
        ));

        $product = $repository->find($productId);

        $this->assertNotNull($product);
        $this->assertSame('iPhone 15', $product->name());
        $this->assertSame(99900, $product->price()->amount());
        $this->assertSame('USD', $product->price()->currency());
        $this->assertSame(10, $product->stock());
    }

    public function test_it_does_not_create_product_with_empty_name(): void
    {
        $repository = new InMemoryProductRepository();

        $handler = new CreateProductHandler($repository);

        $this->expectException(DomainException::class);

        $handler->handle(new CreateProductCommand(
            name: '',
            priceAmount: 99900,
            currency: 'USD',
            stock: 10,
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
