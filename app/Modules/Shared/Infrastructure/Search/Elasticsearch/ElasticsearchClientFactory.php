<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Search\Elasticsearch;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;

final class ElasticsearchClientFactory
{
    public function make(): Client
    {
        return ClientBuilder::create()
            ->setHosts((array) config('elasticsearch.hosts'))
            ->build();
    }
}
