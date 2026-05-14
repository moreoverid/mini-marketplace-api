<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class StoreProductRequest extends FormRequest
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
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'price_amount' => ['required', 'integer', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'stock' => ['required', 'integer', 'min:0'],
        ];
    }

    public function name(): string
    {
        return (string) $this->validated('name');
    }

    public function priceAmount(): int
    {
        return (int) $this->validated('price_amount');
    }

    public function currency(): string
    {
        return strtoupper((string) $this->validated('currency'));
    }

    public function stock(): int
    {
        return (int) $this->validated('stock');
    }
}
