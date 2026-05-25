<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Repositories;

use App\Modules\Catalog\Domain\Entities\Product;
use App\Modules\Catalog\Domain\Repositories\ProductRepository;
use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Catalog\Domain\ValueObjects\ProductId;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Models\ProductModel;

final class EloquentProductRepository implements ProductRepository
{
    public function find(ProductId $id): ?Product
    {
        $model = ProductModel::query()->find($id->value());

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function save(Product $product): void
    {
        ProductModel::query()->updateOrCreate(
            [
                'id' => $product->id()->value(),
            ],
            [
                'name' => $product->name(),
                'price_amount' => $product->price()->amount(),
                'currency' => $product->price()->currency(),
                'stock' => $product->stock(),
            ],
        );
    }

    private function toDomain(ProductModel $model): Product
    {
        return new Product(
            id: ProductId::fromString($model->id),
            name: $model->name,
            price: new Money(
                amount: $model->price_amount,
                currency: $model->currency,
            ),
            stock: $model->stock,
        );
    }
}
