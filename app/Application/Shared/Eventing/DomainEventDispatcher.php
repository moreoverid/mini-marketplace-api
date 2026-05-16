<?php

declare(strict_types=1);

namespace App\Application\Shared\Eventing;

use App\Domain\Shared\Events\DomainEvent;

interface DomainEventDispatcher
{
    public function dispatch(DomainEvent ...$events): void;
}
