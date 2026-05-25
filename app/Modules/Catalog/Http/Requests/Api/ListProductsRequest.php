<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

final class ListProductsRequest extends FormRequest
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
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }

    public function page(): int
    {
        return (int) ($this->validated('page') ?? 1);
    }

    public function perPage(): int
    {
        return (int) ($this->validated('per_page') ?? 15);
    }

    public function search(): ?string
    {
        $search = $this->validated('search') ?? null;

        if ($search === null) {
            return null;
        }

        $search = trim((string) $search);

        return $search === '' ? null : $search;
    }
}
