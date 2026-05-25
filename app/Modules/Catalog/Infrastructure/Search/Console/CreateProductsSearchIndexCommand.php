<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Search\Console;

use App\Modules\Catalog\Infrastructure\Search\Elasticsearch\ProductSearchIndexManager;
use Illuminate\Console\Command;

final class CreateProductsSearchIndexCommand extends Command
{
    protected $signature = 'search:products:create-index {--force : Delete existing index before creating a new one}';

    protected $description = 'Create the Elasticsearch index for product search.';

    public function handle(ProductSearchIndexManager $indexes): int
    {
        $created = $indexes->create(force: (bool) $this->option('force'));

        if (! $created) {
            $this->warn("Index [{$indexes->indexName()}] already exists. Use --force to recreate it.");

            return self::SUCCESS;
        }

        $this->info("Index [{$indexes->indexName()}] has been created.");

        return self::SUCCESS;
    }
}
