<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Search\Console;

use App\Modules\Catalog\Domain\Entities\Product;
use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Catalog\Domain\ValueObjects\ProductId;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Models\ProductModel;
use App\Modules\Catalog\Infrastructure\Search\Elasticsearch\ElasticsearchProductSearchIndexer;
use App\Modules\Catalog\Infrastructure\Search\Elasticsearch\ProductSearchIndexManager;
use Illuminate\Console\Command;

final class ReindexProductsSearchCommand extends Command
{
    protected $signature = 'search:products:reindex {--fresh : Recreate the index before reindexing}';

    protected $description = 'Rebuild the Elasticsearch product search read model from PostgreSQL.';

    public function handle(
        ProductSearchIndexManager $indexes,
        ElasticsearchProductSearchIndexer $indexer,
    ): int {
        if ((bool) $this->option('fresh')) {
            $indexes->create(force: true);
        } elseif (! $indexes->exists()) {
            $indexes->create();
        }

        $indexed = 0;

        ProductModel::query()
            ->orderBy('created_at')
            ->chunk(100, function ($products) use ($indexer, &$indexed): void {
                foreach ($products as $productModel) {
                    $indexer->index(new Product(
                        id: ProductId::fromString((string) $productModel->id),
                        name: (string) $productModel->name,
                        price: new Money(
                            amount: (int) $productModel->price_amount,
                            currency: (string) $productModel->currency,
                        ),
                        stock: (int) $productModel->stock,
                    ));

                    $indexed++;
                }
            });

        $this->info("Indexed {$indexed} products into [{$indexes->indexName()}].");

        return self::SUCCESS;
    }
}
