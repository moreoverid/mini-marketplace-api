<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Domain\Events;

use App\Modules\Ordering\Domain\ValueObjects\OrderId;
use App\Modules\Shared\Domain\Events\DomainEvent;
use DateTimeImmutable;

final readonly class OrderPaid implements DomainEvent
{
    private DateTimeImmutable $occurredAt;

    public function __construct(
        private OrderId $orderId,
        ?DateTimeImmutable $occurredAt = null,
    ) {
        $this->occurredAt = $occurredAt ?? new DateTimeImmutable();
    }

    public function orderId(): OrderId
    {
        return $this->orderId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
