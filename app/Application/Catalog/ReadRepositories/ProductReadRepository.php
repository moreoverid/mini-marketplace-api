<?php

declare(strict_types=1);

namespace App\Application\Catalog\ReadRepositories;

use App\Application\Catalog\Queries\ListProductsQuery;
use App\Application\Catalog\ReadModels\PaginatedProducts;

interface ProductReadRepository
{
    public function paginate(ListProductsQuery $query): PaginatedProducts;
}
