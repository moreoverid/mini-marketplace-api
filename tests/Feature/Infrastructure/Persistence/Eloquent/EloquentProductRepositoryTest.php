<?php

declare(strict_types=1);

namespace Tests\Feature\Infrastructure\Persistence\Eloquent;

use App\Domain\Catalog\Entities\Product;
use App\Domain\Catalog\Repositories\ProductRepository;
use App\Domain\Catalog\ValueObjects\Money;
use App\Domain\Catalog\ValueObjects\ProductId;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class EloquentProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_saves_and_finds_product(): void
    {
        $repository = $this->app->make(ProductRepository::class);

        $id = ProductId::generate();

        $product = new Product(
            id: $id,
            name: 'iPhone 15',
            price: new Money(99900, 'USD'),
            stock: 10,
        );

        $repository->save($product);

        $foundProduct = $repository->find($id);

        $this->assertNotNull($foundProduct);
        $this->assertTrue($id->equals($foundProduct->id()));
        $this->assertSame('iPhone 15', $foundProduct->name());
        $this->assertSame(99900, $foundProduct->price()->amount());
        $this->assertSame('USD', $foundProduct->price()->currency());
        $this->assertSame(10, $foundProduct->stock());
    }

    public function test_it_returns_null_when_product_does_not_exist(): void
    {
        $repository = $this->app->make(ProductRepository::class);

        $product = $repository->find(ProductId::generate());

        $this->assertNull($product);
    }
}
