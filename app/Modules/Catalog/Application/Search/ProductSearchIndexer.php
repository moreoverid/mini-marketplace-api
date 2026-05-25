<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\Search;

use App\Modules\Catalog\Domain\Entities\Product;

interface ProductSearchIndexer
{
    public function index(Product $product): void;
}
