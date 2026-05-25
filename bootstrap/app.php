<?php

use App\Modules\Catalog\Infrastructure\Search\Console\CreateProductsSearchIndexCommand;
use App\Modules\Catalog\Infrastructure\Search\Console\DeleteProductsSearchIndexCommand;
use App\Modules\Catalog\Infrastructure\Search\Console\ReindexProductsSearchCommand;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        CreateProductsSearchIndexCommand::class,
        DeleteProductsSearchIndexCommand::class,
        ReindexProductsSearchCommand::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
