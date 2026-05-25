<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ListOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'nullable', 'string', Rule::in(['pending', 'paid', 'cancelled'])],
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

    public function status(): ?string
    {
        $status = $this->validated('status') ?? null;

        if ($status === null) {
            return null;
        }

        $status = trim((string) $status);

        return $status === '' ? null : $status;
    }
}
