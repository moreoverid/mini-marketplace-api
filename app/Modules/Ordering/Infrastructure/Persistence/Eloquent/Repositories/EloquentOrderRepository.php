<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Infrastructure\Persistence\Eloquent\Repositories;

use App\Modules\Catalog\Domain\ValueObjects\Money;
use App\Modules\Catalog\Domain\ValueObjects\ProductId;
use App\Modules\Ordering\Domain\Entities\Order;
use App\Modules\Ordering\Domain\Entities\OrderItem;
use App\Modules\Ordering\Domain\Repositories\OrderRepository;
use App\Modules\Ordering\Domain\ValueObjects\OrderId;
use App\Modules\Ordering\Domain\ValueObjects\OrderStatus;
use App\Modules\Ordering\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use Illuminate\Support\Facades\DB;

final class EloquentOrderRepository implements OrderRepository
{
    public function find(OrderId $id): ?Order
    {
        $model = OrderModel::query()
            ->with('items')
            ->find($id->value());

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function save(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            /** @var OrderModel $model */
            $model = OrderModel::query()->updateOrCreate(
                ['id' => $order->id()->value()],
                ['status' => $order->status()->value()],
            );

            $model->items()->delete();

            foreach ($order->items() as $item) {
                $model->items()->create([
                    'product_id' => $item->productId()->value(),
                    'unit_price_amount' => $item->unitPrice()->amount(),
                    'currency' => $item->unitPrice()->currency(),
                    'quantity' => $item->quantity(),
                ]);
            }
        });
    }

    private function toDomain(OrderModel $model): Order
    {
        $items = $model->items
            ->map(static fn ($item): OrderItem => new OrderItem(
                productId: ProductId::fromString((string) $item->product_id),
                unitPrice: new Money(
                    amount: (int) $item->unit_price_amount,
                    currency: (string) $item->currency,
                ),
                quantity: (int) $item->quantity,
            ))
            ->values()
            ->all();

        return Order::restore(
            id: OrderId::fromString((string) $model->id),
            status: OrderStatus::fromString((string) $model->status),
            items: $items,
        );
    }
}
