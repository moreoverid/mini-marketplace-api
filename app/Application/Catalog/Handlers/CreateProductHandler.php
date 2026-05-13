<?php

declare(strict_types=1);

namespace App\Application\Catalog\Handlers;

use App\Application\Catalog\Commands\CreateProductCommand;
use App\Domain\Catalog\Entities\Product;
use App\Domain\Catalog\Repositories\ProductRepository;
use App\Domain\Catalog\ValueObjects\Money;
use App\Domain\Catalog\ValueObjects\ProductId;

final class CreateProductHandler
{
    public function __construct(
        private ProductRepository $products
    ) {}

    public function handle(CreateProductCommand $command): ProductId
    {
        $product = new Product(
            id: ProductId::generate(),
            name: $command->name,
            price: new Money($command->priceAmount, $command->currency),
            stock: $command->stock,
        );

        $this->products->save($product);

        return $product->id();
    }
}