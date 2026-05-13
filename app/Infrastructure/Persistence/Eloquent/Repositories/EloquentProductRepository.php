<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Catalog\Entities\Product;
use App\Domain\Catalog\Repositories\ProductRepository;
use App\Domain\Catalog\ValueObjects\Money;
use App\Domain\Catalog\ValueObjects\ProductId;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;

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