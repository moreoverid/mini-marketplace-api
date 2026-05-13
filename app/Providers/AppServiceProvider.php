<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\Catalog\Repositories\ProductRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
