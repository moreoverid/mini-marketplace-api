<?php

declare(strict_types=1);

namespace App\Application\Ordering\ReadRepositories;

use App\Application\Ordering\Queries\ListOrdersQuery;
use App\Application\Ordering\ReadModels\PaginatedOrders;

interface OrderReadRepository
{
    public function paginate(ListOrdersQuery $query): PaginatedOrders;
}
