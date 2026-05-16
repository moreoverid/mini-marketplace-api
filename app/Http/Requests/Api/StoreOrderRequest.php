<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<string>>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'uuid', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return list<array{product_id: string, quantity: int}>
     */
    public function items(): array
    {
        $items = $this->validated('items');

        return array_map(
            static fn (array $item): array => [
                'product_id' => (string) $item['product_id'],
                'quantity' => (int) $item['quantity'],
            ],
            $items,
        );
    }
}
