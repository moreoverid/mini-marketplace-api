<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Application\Ordering\Queries\ListOrdersQuery;
use App\Application\Ordering\ReadModels\OrderListItem;
use App\Application\Ordering\ReadModels\PaginatedOrders;
use App\Application\Ordering\ReadRepositories\OrderReadRepository;
use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;

final class EloquentOrderReadRepository implements OrderReadRepository
{
    public function paginate(ListOrdersQuery $query): PaginatedOrders
    {
        $builder = OrderModel::query()
            ->with('items')
            ->orderByDesc('created_at');

        if ($query->status !== null && $query->status !== '') {
            $builder->where('status', $query->status);
        }

        $paginator = $builder->paginate(
            perPage: $query->perPage,
            columns: ['*'],
            pageName: 'page',
            page: $query->page,
        );

        $items = $paginator
            ->getCollection()
            ->map(static function (OrderModel $model): OrderListItem {
                $orderItems = $model->items;

                $firstItem = $orderItems->first();

                $currency = $firstItem !== null
                    ? (string) $firstItem->currency
                    : 'USD';

                $totalAmount = $orderItems->sum(
                    static fn ($item): int => (int) $item->unit_price_amount * (int) $item->quantity,
                );

                return new OrderListItem(
                    id: (string) $model->id,
                    status: (string) $model->status,
                    totalAmount: $totalAmount,
                    currency: $currency,
                    itemsCount: $orderItems->count(),
                    createdAt: $model->created_at?->toISOString(),
                );
            })
            ->values()
            ->all();

        return new PaginatedOrders(
            items: $items,
            total: $paginator->total(),
            perPage: $paginator->perPage(),
            currentPage: $paginator->currentPage(),
            lastPage: $paginator->lastPage(),
        );
    }
}
