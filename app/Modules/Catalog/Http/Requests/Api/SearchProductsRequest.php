<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class SearchProductsRequest extends FormRequest
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
            'query' => ['required', 'string', 'min:1', 'max:255'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function queryText(): string
    {
        return trim((string) $this->validated('query'));
    }

    public function page(): int
    {
        return (int) ($this->validated('page') ?? 1);
    }

    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? 15);
    }
}
