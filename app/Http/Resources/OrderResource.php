<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Ordering\Entities\Order;
use App\Domain\Ordering\Entities\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Order $resource
 */
final class OrderResource extends JsonResource
{
    /**
     * @return array{
     *     id: string,
     *     status: string,
     *     items: list<array{
     *         product_id: string,
     *         quantity: int,
     *         unit_price: array{amount: int, currency: string},
     *         subtotal: array{amount: int, currency: string}
     *     }>,
     *     total: array{amount: int, currency: string}
     * }
     */
    public function toArray(Request $request): array
    {
        $total = $this->resource->total();

        return [
            'id' => $this->resource->id()->value(),
            'status' => $this->resource->status()->value(),
            'items' => array_map(
                static fn (OrderItem $item): array => [
                    'product_id' => $item->productId()->value(),
                    'quantity' => $item->quantity(),
                    'unit_price' => [
                        'amount' => $item->unitPrice()->amount(),
                        'currency' => $item->unitPrice()->currency(),
                    ],
                    'subtotal' => [
                        'amount' => $item->subtotal()->amount(),
                        'currency' => $item->subtotal()->currency(),
                    ],
                ],
                $this->resource->items(),
            ),
            'total' => [
                'amount' => $total->amount(),
                'currency' => $total->currency(),
            ],
        ];
    }
}