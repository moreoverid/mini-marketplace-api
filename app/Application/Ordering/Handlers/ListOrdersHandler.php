<?php

declare(strict_types=1);

namespace App\Application\Ordering\Handlers;

use App\Application\Ordering\Queries\ListOrdersQuery;
use App\Application\Ordering\ReadModels\PaginatedOrders;
use App\Application\Ordering\ReadRepositories\OrderReadRepository;

final class ListOrdersHandler
{
    public function __construct(
        private OrderReadRepository $orders,
    ) {
    }

    public function handle(ListOrdersQuery $query): PaginatedOrders
    {
        return $this->orders->paginate($query);
    }
}
