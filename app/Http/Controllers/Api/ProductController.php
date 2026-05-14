<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Catalog\Commands\CreateProductCommand;
use App\Application\Catalog\Handlers\CreateProductHandler;
use App\Domain\Catalog\Repositories\ProductRepository;
use App\Domain\Catalog\ValueObjects\ProductId;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreProductRequest;
use App\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ProductController extends Controller
{
    public function store(
        StoreProductRequest $request,
        CreateProductHandler $handler,
    ): JsonResponse {
        $product = $handler->handle(new CreateProductCommand(
            name: $request->name(),
            priceAmount: $request->priceAmount(),
            currency: $request->currency(),
            stock: $request->stock(),
        ));

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(string $id, ProductRepository $products): ProductResource
    {
        $product = $products->find(ProductId::fromString($id));

        abort_if($product === null, Response::HTTP_NOT_FOUND);

        return new ProductResource($product);
    }
}
