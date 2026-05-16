<?php

declare(strict_types=1);

namespace App\Domain\Ordering\ValueObjects;

use DomainException;
use Illuminate\Support\Str;

final readonly class OrderId
{
    private function __construct(
        private string $value,
    ) {
        if (! Str::isUuid($value)) {
            throw new DomainException('Order id must be a valid UUID.');
        }
    }

    public static function generate(): self
    {
        return new self((string) Str::uuid());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
