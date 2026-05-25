<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Application\Handlers;

use App\Modules\Ordering\Application\Queries\ListOrdersQuery;
use App\Modules\Ordering\Application\ReadModels\PaginatedOrders;
use App\Modules\Ordering\Application\ReadRepositories\OrderReadRepository;

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
