<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Search\Elasticsearch;

use App\Modules\Catalog\Application\Queries\SearchProductsQuery;
use App\Modules\Catalog\Application\ReadModels\PaginatedProducts;
use App\Modules\Catalog\Application\ReadModels\ProductListItem;
use App\Modules\Catalog\Application\Search\ProductSearchRepository;
use App\Modules\Shared\Infrastructure\Search\Elasticsearch\ElasticsearchClientFactory;

final class ElasticsearchProductSearchRepository implements ProductSearchRepository
{
    public function __construct(
        private ElasticsearchClientFactory $clients,
    ) {
    }

    public function search(SearchProductsQuery $query): PaginatedProducts
    {
        $from = max(0, ($query->page - 1) * $query->perPage);

        $response = $this->clients->make()->search([
            'index' => (string) config('elasticsearch.products_index'),
            'body' => [
                'from' => $from,
                'size' => $query->perPage,
                'track_total_hits' => true,
                'query' => [
                    'multi_match' => [
                        'query' => $query->query,
                        'fields' => ['name^3', 'id'],
                        'fuzziness' => 'AUTO',
                    ],
                ],
                'sort' => [
                    ['_score' => ['order' => 'desc']],
                    ['name.keyword' => ['order' => 'asc', 'unmapped_type' => 'keyword']],
                ],
            ],
        ])->asArray();

        $total = $this->extractTotal($response);

        $items = array_map(
            static function (array $hit): ProductListItem {
                /** @var array{id?: string, name?: string, price_amount?: int, currency?: string, stock?: int, indexed_at?: string|null} $source */
                $source = $hit['_source'] ?? [];

                return new ProductListItem(
                    id: (string) ($source['id'] ?? ''),
                    name: (string) ($source['name'] ?? ''),
                    priceAmount: (int) ($source['price_amount'] ?? 0),
                    currency: (string) ($source['currency'] ?? 'USD'),
                    stock: (int) ($source['stock'] ?? 0),
                    createdAt: $source['indexed_at'] ?? null,
                );
            },
            $response['hits']['hits'] ?? [],
        );

        return new PaginatedProducts(
            items: array_values($items),
            total: $total,
            perPage: $query->perPage,
            currentPage: $query->page,
            lastPage: (int) max(1, ceil($total / $query->perPage)),
        );
    }

    /**
     * @param array<string, mixed> $response
     */
    private function extractTotal(array $response): int
    {
        $total = $response['hits']['total'] ?? 0;

        if (is_array($total)) {
            return (int) ($total['value'] ?? 0);
        }

        return (int) $total;
    }
}
