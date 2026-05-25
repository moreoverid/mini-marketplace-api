<?php

declare(strict_types=1);

$hosts = env('ELASTICSEARCH_HOSTS', env('ELASTICSEARCH_HOST', 'http://elasticsearch:9200'));

return [
    'hosts' => array_values(array_filter(array_map(
        static fn (string $host): string => trim($host),
        explode(',', (string) $hosts),
    ))),

    'products_index' => env('ELASTICSEARCH_PRODUCTS_INDEX', 'products'),
];
