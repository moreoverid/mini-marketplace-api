<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Http\Resources;

use App\Modules\Ordering\Application\ReadModels\OrderListItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read OrderListItem $resource
 */
final class OrderListItemResource extends JsonResource
{
    /**
     * @return array{
     *     id: string,
     *     status: string,
     *     total: array{amount: int, currency: string},
     *     items_count: int,
     *     created_at: string|null
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'status' => $this->resource->status,
            'total' => [
                'amount' => $this->resource->totalAmount,
                'currency' => $this->resource->currency,
            ],
            'items_count' => $this->resource->itemsCount,
            'created_at' => $this->resource->createdAt,
        ];
    }
}
