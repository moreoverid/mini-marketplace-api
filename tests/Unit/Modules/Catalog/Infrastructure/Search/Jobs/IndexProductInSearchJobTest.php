<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Catalog\Infrastructure\Search\Jobs;

use App\Modules\Catalog\Application\Search\ProductSearchIndexer;
use App\Modules\Catalog\Domain\Entities\Product;
use App\Modules\Catalog\Domain\Repositories\ProductRepository;
use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Catalog\Domain\ValueObjects\ProductId;
use App\Modules\Catalog\Infrastructure\Search\Jobs\IndexProductInSearchJob;
use PHPUnit\Framework\TestCase;

final class IndexProductInSearchJobTest extends TestCase
{
    public function test_it_indexes_existing_product(): void
    {
        $products = new SearchJobInMemoryProductRepository();
        $indexer = new SearchJobSpyProductSearchIndexer();

        $product = new Product(
            id: ProductId::generate(),
            name: 'iPhone 15',
            price: new Money(99900, 'USD'),
            stock: 10,
        );

        $products->save($product);

        $job = new IndexProductInSearchJob($product->id()->value());
        $job->handle($products, $indexer);

        $this->assertCount(1, $indexer->products);
        $this->assertTrue($product->id()->equals($indexer->products[0]->id()));
    }

    public function test_it_ignores_missing_product(): void
    {
        $products = new SearchJobInMemoryProductRepository();
        $indexer = new SearchJobSpyProductSearchIndexer();

        $job = new IndexProductInSearchJob(ProductId::generate()->value());
        $job->handle($products, $indexer);

        $this->assertSame([], $indexer->products);
    }
}

final class SearchJobInMemoryProductRepository implements ProductRepository
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

final class SearchJobSpyProductSearchIndexer implements ProductSearchIndexer
{
    /**
     * @var list<Product>
     */
    public array $products = [];

    public function index(Product $product): void
    {
        $this->products[] = $product;
    }
}
