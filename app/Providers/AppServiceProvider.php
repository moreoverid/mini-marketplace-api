<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Catalog\Repositories\ProductRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use App\Application\Catalog\ReadRepositories\ProductReadRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductReadRepository;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
