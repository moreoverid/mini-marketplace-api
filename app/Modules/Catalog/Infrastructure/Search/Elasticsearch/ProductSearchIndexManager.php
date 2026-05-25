<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Search\Elasticsearch;

use App\Modules\Shared\Infrastructure\Search\Elasticsearch\ElasticsearchClientFactory;

final class ProductSearchIndexManager
{
    public function __construct(
        private ElasticsearchClientFactory $clients,
    ) {
    }

    public function exists(): bool
    {
        $response = $this->clients->make()->indices()->exists([
            'index' => $this->indexName(),
        ]);

        if (method_exists($response, 'asBool')) {
            return $response->asBool();
        }

        return $response->getStatusCode() === 200;
    }

    public function create(bool $force = false): bool
    {
        if ($this->exists()) {
            if (! $force) {
                return false;
            }

            $this->delete();
        }

        $this->clients->make()->indices()->create([
            'index' => $this->indexName(),
            'body' => [
                'settings' => [
                    'number_of_shards' => 1,
                    'number_of_replicas' => 0,
                    'analysis' => [
                        'analyzer' => [
                            'product_name_analyzer' => [
                                'type' => 'standard',
                                'stopwords' => '_none_',
                            ],
                        ],
                    ],
                ],
                'mappings' => [
                    'properties' => [
                        'id' => ['type' => 'keyword'],
                        'name' => [
                            'type' => 'text',
                            'analyzer' => 'product_name_analyzer',
                            'fields' => [
                                'keyword' => ['type' => 'keyword'],
                            ],
                        ],
                        'price_amount' => ['type' => 'long'],
                        'currency' => ['type' => 'keyword'],
                        'stock' => ['type' => 'integer'],
                        'indexed_at' => ['type' => 'date'],
                    ],
                ],
            ],
        ]);

        return true;
    }

    public function delete(): bool
    {
        if (! $this->exists()) {
            return false;
        }

        $this->clients->make()->indices()->delete([
            'index' => $this->indexName(),
        ]);

        return true;
    }

    public function indexName(): string
    {
        return (string) config('elasticsearch.products_index');
    }
}
