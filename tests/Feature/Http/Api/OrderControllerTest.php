<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api;

use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Jobs\Ordering\RecordOrderPaidAuditLogJob;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_order(): void
    {
        $productId = (string) Str::uuid();

        ProductModel::query()->create([
            'id' => $productId,
            'name' => 'iPhone 15',
            'price_amount' => 99900,
            'currency' => 'USD',
            'stock' => 10,
        ]);

        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.items.0.product_id', $productId)
            ->assertJsonPath('data.items.0.quantity', 2)
            ->assertJsonPath('data.items.0.unit_price.amount', 99900)
            ->assertJsonPath('data.total.amount', 199800)
            ->assertJsonPath('data.total.currency', 'USD');

        $orderId = $response->json('data.id');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $orderId,
            'product_id' => $productId,
            'unit_price_amount' => 99900,
            'currency' => 'USD',
            'quantity' => 2,
        ]);
    }

    public function test_it_validates_required_fields(): void
    {
        $response = $this->postJson('/api/orders', []);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'items',
            ]);
    }

    public function test_it_validates_order_items(): void
    {
        $response = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => 'not-uuid',
                    'quantity' => 0,
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'items.0.product_id',
                'items.0.quantity',
            ]);
    }

    public function test_it_shows_order(): void
    {
        $productId = (string) Str::uuid();

        ProductModel::query()->create([
            'id' => $productId,
            'name' => 'iPhone 15',
            'price_amount' => 99900,
            'currency' => 'USD',
            'stock' => 10,
        ]);

        $createResponse = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 2,
                ],
            ],
        ]);

        $orderId = $createResponse->json('data.id');

        $response = $this->getJson("/api/orders/{$orderId}");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $orderId)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.items.0.product_id', $productId)
            ->assertJsonPath('data.total.amount', 199800);
    }

    public function test_it_returns_404_when_order_does_not_exist(): void
    {
        $response = $this->getJson('/api/orders/11111111-1111-1111-1111-111111111111');

        $response->assertNotFound();
    }

    public function test_it_pays_order(): void
    {
        $productId = (string) Str::uuid();

        ProductModel::query()->create([
            'id' => $productId,
            'name' => 'iPhone 15',
            'price_amount' => 99900,
            'currency' => 'USD',
            'stock' => 10,
        ]);

        $createResponse = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 2,
                ],
            ],
        ]);

        $orderId = $createResponse->json('data.id');

        Queue::fake();

        $response = $this->patchJson("/api/orders/{$orderId}/pay");

        $response
            ->assertOk()
            ->assertJsonPath('data.id', $orderId)
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.total.amount', 199800);

        Queue::assertPushed(
            RecordOrderPaidAuditLogJob::class,
            static fn (RecordOrderPaidAuditLogJob $job): bool => $job->orderId === $orderId,
        );

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'paid',
        ]);
    }

    public function test_it_returns_404_when_paying_unknown_order(): void
    {
        $response = $this->patchJson('/api/orders/11111111-1111-1111-1111-111111111111/pay');

        $response->assertNotFound();
    }

    public function test_it_returns_404_when_paying_order_with_invalid_id(): void
    {
        $response = $this->patchJson('/api/orders/not-a-uuid/pay');

        $response->assertNotFound();
    }

    public function test_it_returns_409_when_order_is_already_paid(): void
    {
        $productId = (string) Str::uuid();

        ProductModel::query()->create([
            'id' => $productId,
            'name' => 'iPhone 15',
            'price_amount' => 99900,
            'currency' => 'USD',
            'stock' => 10,
        ]);

        $createResponse = $this->postJson('/api/orders', [
            'items' => [
                [
                    'product_id' => $productId,
                    'quantity' => 2,
                ],
            ],
        ]);

        $orderId = $createResponse->json('data.id');

        $this->patchJson("/api/orders/{$orderId}/pay")
            ->assertOk();

        $this->patchJson("/api/orders/{$orderId}/pay")
            ->assertConflict();
    }
}
