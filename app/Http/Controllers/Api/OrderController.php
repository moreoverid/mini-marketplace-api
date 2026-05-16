<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Application\Ordering\Commands\CreateOrderCommand;
use App\Application\Ordering\Handlers\CreateOrderHandler;
use App\Domain\Ordering\Repositories\OrderRepository;
use App\Domain\Ordering\ValueObjects\OrderId;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use DomainException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

final class OrderController extends Controller
{
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
}
