<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Search\Elasticsearch;

use App\Modules\Catalog\Application\Search\ProductSearchIndexer;
use App\Modules\Catalog\Domain\Entities\Product;
use App\Modules\Shared\Infrastructure\Search\Elasticsearch\ElasticsearchClientFactory;
use DateTimeImmutable;

final class ElasticsearchProductSearchIndexer implements ProductSearchIndexer
{
    public function __construct(
        private ElasticsearchClientFactory $clients,
    ) {
    }

    public function index(Product $product): void
    {
        $this->clients->make()->index([
            'index' => (string) config('elasticsearch.products_index'),
            'id' => $product->id()->value(),
            'body' => [
                'id' => $product->id()->value(),
                'name' => $product->name(),
                'price_amount' => $product->price()->amount(),
                'currency' => $product->price()->currency(),
                'stock' => $product->stock(),
                'indexed_at' => (new DateTimeImmutable())->format(DATE_ATOM),
            ],
        ]);
    }
}
