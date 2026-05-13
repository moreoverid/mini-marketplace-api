<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Repositories;

use App\Domain\Catalog\Entities\Product;
use App\Domain\Catalog\ValueObjects\ProductId;

interface ProductRepository
{
    public function find(ProductId $id): ?Product;

    public function save(Product $product): void;
}
