<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Http\Controllers\Api;

use App\Modules\Ordering\Application\Commands\CreateOrderCommand;
use App\Modules\Ordering\Application\Handlers\CreateOrderHandler;
use App\Modules\Ordering\Domain\Repositories\OrderRepository;
use App\Modules\Ordering\Domain\ValueObjects\OrderId;
use App\Http\Controllers\Controller;
use App\Modules\Ordering\Http\Requests\Api\StoreOrderRequest;
use App\Modules\Ordering\Http\Resources\OrderResource;
use App\Modules\Ordering\Application\Commands\PayOrderCommand;
use App\Modules\Ordering\Application\Exceptions\OrderNotFoundException;
use App\Modules\Ordering\Application\Handlers\PayOrderHandler;
use App\Modules\Ordering\Application\Handlers\ListOrdersHandler;
use App\Modules\Ordering\Application\Queries\ListOrdersQuery;
use App\Modules\Ordering\Http\Requests\Api\ListOrdersRequest;
use App\Modules\Ordering\Http\Resources\OrderListItemResource;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class OrderController extends Controller
{
    public function index(
        ListOrdersRequest $request,
        ListOrdersHandler $handler,
    ): AnonymousResourceCollection {
        $orders = $handler->handle(new ListOrdersQuery(
            page: $request->page(),
            perPage: $request->perPage(),
            status: $request->status(),
        ));

        return OrderListItemResource::collection($orders->items)
            ->additional([
                'meta' => [
                    'total' => $orders->total,
                    'per_page' => $orders->perPage,
                    'current_page' => $orders->currentPage,
                    'last_page' => $orders->lastPage,
                ],
            ]);
    }

    public function store(
        StoreOrderRequest $request,
        CreateOrderHandler $handler,
    ): JsonResponse {
        $order = $handler->handle(new CreateOrderCommand(
            items: $request->items(),
        ));

        return (new OrderResource($order))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(string $id, OrderRepository $orders): OrderResource
    {
        try {
            $orderId = OrderId::fromString($id);
        } catch (DomainException) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $order = $orders->find($orderId);

        abort_if($order === null, Response::HTTP_NOT_FOUND);

        return new OrderResource($order);
    }

    public function pay(
        string $id,
        PayOrderHandler $handler,
    ): OrderResource|JsonResponse {
        try {
            $orderId = OrderId::fromString($id);
        } catch (DomainException) {
            abort(Response::HTTP_NOT_FOUND);
        }

        try {
            $order = $handler->handle(new PayOrderCommand($orderId));
        } catch (OrderNotFoundException) {
            abort(Response::HTTP_NOT_FOUND);
        } catch (DomainException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], Response::HTTP_CONFLICT);
        }

        return new OrderResource($order);
    }
}
