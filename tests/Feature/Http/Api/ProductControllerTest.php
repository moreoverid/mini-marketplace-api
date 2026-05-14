<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api;

use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

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
}
