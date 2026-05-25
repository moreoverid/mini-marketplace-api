<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\Handlers;

use App\Modules\Catalog\Application\Queries\ListProductsQuery;
use App\Modules\Catalog\Application\ReadModels\PaginatedProducts;
use App\Modules\Catalog\Application\ReadRepositories\ProductReadRepository;

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
