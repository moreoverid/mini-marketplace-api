<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\ReadRepositories;

use App\Modules\Catalog\Application\Queries\ListProductsQuery;
use App\Modules\Catalog\Application\ReadModels\PaginatedProducts;

interface ProductReadRepository
{
    public function paginate(ListProductsQuery $query): PaginatedProducts;
}
