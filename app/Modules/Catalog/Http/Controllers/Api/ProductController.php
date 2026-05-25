<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Http\Controllers\Api;

use App\Modules\Catalog\Application\Commands\CreateProductCommand;
use App\Modules\Catalog\Application\Handlers\CreateProductHandler;
use App\Modules\Catalog\Domain\Repositories\ProductRepository;
use App\Modules\Catalog\Domain\ValueObjects\ProductId;
use App\Http\Controllers\Controller;
use App\Modules\Catalog\Http\Requests\Api\StoreProductRequest;
use App\Modules\Catalog\Http\Resources\ProductResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use App\Modules\Catalog\Application\Handlers\ListProductsHandler;
use App\Modules\Catalog\Application\Queries\ListProductsQuery;
use App\Modules\Catalog\Http\Requests\Api\ListProductsRequest;
use App\Modules\Catalog\Http\Resources\ProductListItemResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class ProductController extends Controller
{
    public function index(
        ListProductsRequest $request,
        ListProductsHandler $handler,
    ): AnonymousResourceCollection {
        $products = $handler->handle(new ListProductsQuery(
            page: $request->page(),
            perPage: $request->perPage(),
            search: $request->search(),
        ));

        return ProductListItemResource::collection($products->items)
            ->additional([
                'meta' => [
                    'total' => $products->total,
                    'per_page' => $products->perPage,
                    'current_page' => $products->currentPage,
                    'last_page' => $products->lastPage,
                ],
            ]);
    }

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
