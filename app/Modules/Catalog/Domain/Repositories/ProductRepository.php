<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Domain\Repositories;

use App\Modules\Catalog\Domain\Entities\Product;
use App\Modules\Catalog\Domain\ValueObjects\ProductId;

interface ProductRepository
{
    public function find(ProductId $id): ?Product;

    public function save(Product $product): void;
}
