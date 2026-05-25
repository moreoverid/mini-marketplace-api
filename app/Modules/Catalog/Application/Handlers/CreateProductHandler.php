<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\Handlers;

use App\Modules\Catalog\Application\Commands\CreateProductCommand;
use App\Modules\Catalog\Application\Search\ProductSearchIndexScheduler;
use App\Modules\Catalog\Domain\Entities\Product;
use App\Modules\Catalog\Domain\Repositories\ProductRepository;
use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Catalog\Domain\ValueObjects\ProductId;

final class CreateProductHandler
{
    public function __construct(
        private ProductRepository $products,
        private ProductSearchIndexScheduler $searchIndexer,
    ) {
    }

    public function handle(CreateProductCommand $command): Product
    {
        $product = new Product(
            id: ProductId::generate(),
            name: $command->name,
            price: new Money($command->priceAmount, $command->currency),
            stock: $command->stock,
        );

        $this->products->save($product);
        $this->searchIndexer->schedule($product);

        return $product;
    }
}
