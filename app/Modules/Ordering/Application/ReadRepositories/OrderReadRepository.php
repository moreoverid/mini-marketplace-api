<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Application\ReadRepositories;

use App\Modules\Ordering\Application\Queries\ListOrdersQuery;
use App\Modules\Ordering\Application\ReadModels\PaginatedOrders;

interface OrderReadRepository
{
    public function paginate(ListOrdersQuery $query): PaginatedOrders;
}
