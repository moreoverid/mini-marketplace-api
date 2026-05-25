<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Search\Jobs;

use App\Modules\Catalog\Application\Search\ProductSearchIndexer;
use App\Modules\Catalog\Domain\Repositories\ProductRepository;
use App\Modules\Catalog\Domain\ValueObjects\ProductId;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class IndexProductInSearchJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public readonly string $productId,
    ) {
        $this->onQueue('search');
    }

    public function handle(
        ProductRepository $products,
        ProductSearchIndexer $indexer,
    ): void {
        $product = $products->find(ProductId::fromString($this->productId));

        if ($product === null) {
            return;
        }

        $indexer->index($product);
    }
}
