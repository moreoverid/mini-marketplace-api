<?php

namespace App\Providers;

use App\Modules\Catalog\Application\ReadRepositories\ProductReadRepository;
use App\Modules\Catalog\Application\Search\ProductSearchIndexer;
use App\Modules\Catalog\Application\Search\ProductSearchIndexScheduler;
use App\Modules\Catalog\Application\Search\ProductSearchRepository;
use App\Modules\Catalog\Domain\Repositories\ProductRepository;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductReadRepository;
use App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use App\Modules\Catalog\Infrastructure\Search\Elasticsearch\ElasticsearchProductSearchIndexer;
use App\Modules\Catalog\Infrastructure\Search\Elasticsearch\ElasticsearchProductSearchRepository;
use App\Modules\Catalog\Infrastructure\Search\QueuedProductSearchIndexer;
use App\Modules\Ordering\Application\ReadRepositories\OrderReadRepository;
use App\Modules\Ordering\Domain\Events\OrderPaid;
use App\Modules\Ordering\Domain\Repositories\OrderRepository;
use App\Modules\Ordering\Infrastructure\Eventing\Listeners\DispatchOrderPaidJobs;
use App\Modules\Ordering\Infrastructure\Persistence\Eloquent\Repositories\EloquentOrderReadRepository;
use App\Modules\Ordering\Infrastructure\Persistence\Eloquent\Repositories\EloquentOrderRepository;
use App\Modules\Shared\Application\Eventing\DomainEventDispatcher;
use App\Modules\Shared\Application\Messaging\IntegrationEventPublisher;
use App\Modules\Shared\Infrastructure\Eventing\LaravelDomainEventDispatcher;
use App\Modules\Shared\Infrastructure\Messaging\RabbitMq\RabbitMqIntegrationEventPublisher;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            ProductRepository::class,
            EloquentProductRepository::class,
        );

        $this->app->bind(
            ProductReadRepository::class,
            EloquentProductReadRepository::class,
        );

        $this->app->bind(
            ProductSearchIndexScheduler::class,
            QueuedProductSearchIndexer::class,
        );

        $this->app->bind(
            ProductSearchIndexer::class,
            ElasticsearchProductSearchIndexer::class,
        );

        $this->app->bind(
            ProductSearchRepository::class,
            ElasticsearchProductSearchRepository::class,
        );

        $this->app->bind(
            OrderRepository::class,
            EloquentOrderRepository::class,
        );

        $this->app->bind(
            OrderReadRepository::class,
            EloquentOrderReadRepository::class,
        );

        $this->app->bind(
            DomainEventDispatcher::class,
            LaravelDomainEventDispatcher::class,
        );

        $this->app->bind(
            IntegrationEventPublisher::class,
            RabbitMqIntegrationEventPublisher::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            OrderPaid::class,
            DispatchOrderPaidJobs::class,
        );
    }
}
