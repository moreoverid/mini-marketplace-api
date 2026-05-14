<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Domain\Catalog\Entities\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Product $resource
 */
final class ProductResource extends JsonResource
{
    /**
     * @return array{
     *     id: string,
     *     name: string,
     *     price: array{amount: int, currency: string},
     *     stock: int
     * }
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id()->value(),
            'name' => $this->resource->name(),
            'price' => [
                'amount' => $this->resource->price()->amount(),
                'currency' => $this->resource->price()->currency(),
            ],
            'stock' => $this->resource->stock(),
        ];
    }
}
