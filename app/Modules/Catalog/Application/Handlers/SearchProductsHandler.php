<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\Handlers;

use App\Modules\Catalog\Application\Queries\SearchProductsQuery;
use App\Modules\Catalog\Application\ReadModels\PaginatedProducts;
use App\Modules\Catalog\Application\Search\ProductSearchRepository;

final class SearchProductsHandler
{
    public function __construct(
        private ProductSearchRepository $products,
    ) {
    }

    public function handle(SearchProductsQuery $query): PaginatedProducts
    {
        return $this->products->search($query);
    }
}
