<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Domain\ValueObjects;

use DomainException;

final readonly class OrderStatus
{
    private const PENDING = 'pending';
    private const PAID = 'paid';
    private const CANCELLED = 'cancelled';

    private function __construct(
        private string $value,
    ) {
        if (! in_array($value, [self::PENDING, self::PAID, self::CANCELLED], true)) {
            throw new DomainException('Invalid order status.');
        }
    }

    public static function pending(): self
    {
        return new self(self::PENDING);
    }

    public static function paid(): self
    {
        return new self(self::PAID);
    }

    public static function cancelled(): self
    {
        return new self(self::CANCELLED);
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

    public function isPending(): bool
    {
        return $this->value === self::PENDING;
    }

    public function isPaid(): bool
    {
        return $this->value === self::PAID;
    }
}
