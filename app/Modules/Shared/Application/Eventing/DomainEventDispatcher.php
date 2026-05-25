<?php

declare(strict_types=1);

namespace App\Modules\Shared\Application\Eventing;

use App\Modules\Shared\Domain\Events\DomainEvent;

interface DomainEventDispatcher
{
    public function dispatch(DomainEvent ...$events): void;
}
