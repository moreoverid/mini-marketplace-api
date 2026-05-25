<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Search;

use App\Modules\Catalog\Application\Search\ProductSearchIndexScheduler;
use App\Modules\Catalog\Domain\Entities\Product;
use App\Modules\Catalog\Infrastructure\Search\Jobs\IndexProductInSearchJob;

final class QueuedProductSearchIndexer implements ProductSearchIndexScheduler
{
    public function schedule(Product $product): void
    {
        IndexProductInSearchJob::dispatch(
            productId: $product->id()->value(),
        )->afterCommit();
    }
}
