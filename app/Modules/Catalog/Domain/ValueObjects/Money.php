<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Domain\ValueObjects;

use DomainException;

final readonly class Money
{
    public function __construct(
        private int $amount,
        private string $currency,
    ) {
        if ($amount < 0) {
            throw new DomainException('Money amount cannot be negative.');
        }

        if (! preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new DomainException('Currency must be a valid 3-letter code.');
        }
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount
            && $this->currency === $other->currency;
    }
}
