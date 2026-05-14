<?php

declare(strict_types=1);

namespace App\Application\Catalog\Handlers;

use App\Application\Catalog\Queries\ListProductsQuery;
use App\Application\Catalog\ReadModels\PaginatedProducts;
use App\Application\Catalog\ReadRepositories\ProductReadRepository;

final class ListProductsHandler
{
    public function __construct(
        private ProductReadRepository $products,
    ) {
    }

    public function handle(ListProductsQuery $query): PaginatedProducts
    {
        return $this->products->paginate($query);
    }
}
