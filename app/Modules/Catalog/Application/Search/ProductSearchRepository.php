<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\Search;

use App\Modules\Catalog\Application\Queries\SearchProductsQuery;
use App\Modules\Catalog\Application\ReadModels\PaginatedProducts;

interface ProductSearchRepository
{
    public function search(SearchProductsQuery $query): PaginatedProducts;
}
