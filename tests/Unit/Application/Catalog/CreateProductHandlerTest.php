<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Catalog;

use App\Modules\Catalog\Application\Commands\CreateProductCommand;
use App\Modules\Catalog\Application\Handlers\CreateProductHandler;
use App\Modules\Catalog\Application\Search\ProductSearchIndexScheduler;
use App\Modules\Catalog\Domain\Entities\Product;
use App\Modules\Catalog\Domain\Repositories\ProductRepository;
use App\Modules\Catalog\Domain\ValueObjects\ProductId;
use DomainException;
use PHPUnit\Framework\TestCase;

final class CreateProductHandlerTest extends TestCase
{
    public function test_it_creates_product(): void
    {
        $repository = new InMemoryProductRepository();
        $searchIndexer = new SpyProductSearchIndexer();

        $handler = new CreateProductHandler($repository, $searchIndexer);

        $product = $handler->handle(new CreateProductCommand(
            name: 'iPhone 15',
            priceAmount: 99900,
            currency: 'USD',
            stock: 10,
        ));

        $savedProduct = $repository->find($product->id());

        $this->assertNotNull($savedProduct);
        $this->assertTrue($product->id()->equals($savedProduct->id()));

        $this->assertSame('iPhone 15', $savedProduct->name());
        $this->assertSame(99900, $savedProduct->price()->amount());
        $this->assertSame('USD', $savedProduct->price()->currency());
        $this->assertSame(10, $savedProduct->stock());

        $this->assertCount(1, $searchIndexer->products);
        $this->assertTrue($product->id()->equals($searchIndexer->products[0]->id()));
    }

    public function test_it_does_not_create_product_with_empty_name(): void
    {
        $repository = new InMemoryProductRepository();
        $searchIndexer = new SpyProductSearchIndexer();

        $handler = new CreateProductHandler($repository, $searchIndexer);

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

final class SpyProductSearchIndexer implements ProductSearchIndexScheduler
{
    /**
     * @var list<Product>
     */
    public array $products = [];

    public function schedule(Product $product): void
    {
        $this->products[] = $product;
    }
}
