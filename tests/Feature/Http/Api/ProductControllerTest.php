<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api;

use App\Modules\Catalog\Application\Queries\SearchProductsQuery;
use App\Modules\Catalog\Application\ReadModels\PaginatedProducts;
use App\Modules\Catalog\Application\ReadModels\ProductListItem;
use App\Modules\Catalog\Application\Search\ProductSearchIndexScheduler;
use App\Modules\Catalog\Application\Search\ProductSearchRepository;
use App\Modules\Catalog\Domain\Entities\Product;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(
            ProductSearchIndexScheduler::class,
            static fn (): ProductSearchIndexScheduler => new NullProductSearchIndexScheduler(),
        );
    }

    public function test_it_creates_product(): void
    {
        $response = $this->postJson('/api/products', [
            'name' => 'iPhone 15',
            'price_amount' => 99900,
            'currency' => 'USD',
            'stock' => 10,
        ]);

        $response
            ->assertCreated()
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'name',
                    'price' => [
                        'amount',
                        'currency',
                    ],
                    'stock',
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'name' => 'iPhone 15',
            'price_amount' => 99900,
            'currency' => 'USD',
            'stock' => 10,
        ]);
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->postJson('/api/products', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name',
                'price_amount',
                'currency',
                'stock',
            ]);
    }

    public function test_it_shows_product(): void
    {
        $createResponse = $this->postJson('/api/products', [
            'name' => 'iPhone 15',
            'price_amount' => 99900,
            'currency' => 'USD',
            'stock' => 10,
        ]);

        $productId = $createResponse->json('data.id');

        $response = $this->getJson("/api/products/{$productId}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $productId)
            ->assertJsonPath('data.name', 'iPhone 15')
            ->assertJsonPath('data.price.amount', 99900)
            ->assertJsonPath('data.price.currency', 'USD')
            ->assertJsonPath('data.stock', 10);
    }

    public function test_it_returns_404_when_product_does_not_exist(): void
    {
        $response = $this->getJson('/api/products/11111111-1111-1111-1111-111111111111');

        $response->assertNotFound();
    }

    public function test_it_lists_products(): void
    {
        ProductModel::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'iPhone 15',
            'price_amount' => 99900,
            'currency' => 'USD',
            'stock' => 10,
        ]);

        ProductModel::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'MacBook Pro',
            'price_amount' => 249900,
            'currency' => 'USD',
            'stock' => 5,
        ]);

        $response = $this->getJson('/api/products?per_page=10');

        $response
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 2)
            ->assertJsonPath('meta.per_page', 10);
    }

    public function test_it_filters_products_by_search(): void
    {
        ProductModel::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'iPhone 15',
            'price_amount' => 99900,
            'currency' => 'USD',
            'stock' => 10,
        ]);

        ProductModel::query()->create([
            'id' => (string) Str::uuid(),
            'name' => 'MacBook Pro',
            'price_amount' => 249900,
            'currency' => 'USD',
            'stock' => 5,
        ]);

        $response = $this->getJson('/api/products?search=iphone');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'iPhone 15')
            ->assertJsonPath('meta.total', 1);
    }

    public function test_it_searches_products_through_search_read_side(): void
    {
        $this->app->bind(
            ProductSearchRepository::class,
            static fn (): ProductSearchRepository => new FakeProductSearchRepository(),
        );

        $response = $this->getJson('/api/products/search?query=iphone&per_page=10');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', 'product-1')
            ->assertJsonPath('data.0.name', 'iPhone 15')
            ->assertJsonPath('data.0.price.amount', 99900)
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('meta.per_page', 10);
    }

    public function test_it_validates_search_query(): void
    {
        $response = $this->getJson('/api/products/search');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['query']);
    }
}

final class NullProductSearchIndexScheduler implements ProductSearchIndexScheduler
{
    public function schedule(Product $product): void
    {
    }
}

final class FakeProductSearchRepository implements ProductSearchRepository
{
    public function search(SearchProductsQuery $query): PaginatedProducts
    {
        return new PaginatedProducts(
            items: [
                new ProductListItem(
                    id: 'product-1',
                    name: 'iPhone 15',
                    priceAmount: 99900,
                    currency: 'USD',
                    stock: 10,
                    createdAt: '2026-05-25T10:00:00+00:00',
                ),
            ],
            total: 1,
            perPage: $query->perPage,
            currentPage: $query->page,
            lastPage: 1,
        );
    }
}
