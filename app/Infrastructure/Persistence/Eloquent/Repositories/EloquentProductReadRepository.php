<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Catalog\Queries\ListProductsQuery;
use App\Application\Catalog\ReadModels\PaginatedProducts;
use App\Application\Catalog\ReadModels\ProductListItem;
use App\Application\Catalog\ReadRepositories\ProductReadRepository;
use App\Infrastructure\Persistence\Eloquent\Models\ProductModel;

final class EloquentProductReadRepository implements ProductReadRepository
{
    public function paginate(ListProductsQuery $query): PaginatedProducts
    {
        $builder = ProductModel::query()
            ->orderByDesc('created_at');

        if ($query->search !== null && $query->search !== '') {
            $builder->where('name', 'ILIKE', '%' . $query->search . '%');
        }

        $paginator = $builder->paginate(
            perPage: $query->perPage,
            columns: ['*'],
            pageName: 'page',
            page: $query->page,
        );

        $items = $paginator
            ->getCollection()
            ->map(static fn (ProductModel $model): ProductListItem => new ProductListItem(
                id: (string) $model->id,
                name: (string) $model->name,
                priceAmount: (int) $model->price_amount,
                currency: (string) $model->currency,
                stock: (int) $model->stock,
                createdAt: $model->created_at?->toISOString(),
            ))
            ->values()
            ->all();

        return new PaginatedProducts(
            items: $items,
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
        );
    }
}
