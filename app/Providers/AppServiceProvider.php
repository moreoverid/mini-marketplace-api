<?php

namespace App\Providers;

use App\Domain\Catalog\Repositories\ProductRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use App\Application\Catalog\ReadRepositories\ProductReadRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductReadRepository;
use App\Domain\Ordering\Repositories\OrderRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentOrderRepository;
use App\Application\Shared\Eventing\DomainEventDispatcher;
use App\Infrastructure\Eventing\LaravelDomainEventDispatcher;
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
            OrderRepository::class,
            EloquentOrderRepository::class,
        );

        $this->app->bind(
            DomainEventDispatcher::class,
            LaravelDomainEventDispatcher::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
