<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Search\Console;

use App\Modules\Catalog\Infrastructure\Search\Elasticsearch\ProductSearchIndexManager;
use Illuminate\Console\Command;

final class DeleteProductsSearchIndexCommand extends Command
{
    protected $signature = 'search:products:delete-index';

    protected $description = 'Delete the Elasticsearch index for product search.';

    public function handle(ProductSearchIndexManager $indexes): int
    {
        $deleted = $indexes->delete();

        if (! $deleted) {
            $this->warn("Index [{$indexes->indexName()}] does not exist.");

            return self::SUCCESS;
        }

        $this->info("Index [{$indexes->indexName()}] has been deleted.");

        return self::SUCCESS;
    }
}
