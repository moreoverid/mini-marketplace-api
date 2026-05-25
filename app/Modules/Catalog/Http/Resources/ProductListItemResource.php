<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Http\Resources;

use App\Modules\Catalog\Application\ReadModels\ProductListItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read ProductListItem $resource
 */
final class ProductListItemResource extends JsonResource
{
    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     price: array{amount: int, currency: string},
     *     stock: int,
     *     created_at: string|null
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'price' => [
                'amount' => $this->resource->priceAmount,
                'currency' => $this->resource->currency,
            ],
            'stock' => $this->resource->stock,
            'created_at' => $this->resource->createdAt,
        ];
    }
}
