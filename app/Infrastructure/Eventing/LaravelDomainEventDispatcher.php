<?php

declare(strict_types=1);

namespace App\Infrastructure\Eventing;

use App\Application\Shared\Eventing\DomainEventDispatcher;
use App\Domain\Shared\Events\DomainEvent;
use Illuminate\Contracts\Events\Dispatcher;

final class LaravelDomainEventDispatcher implements DomainEventDispatcher
{
    public function __construct(
        private Dispatcher $events,
    ) {
    }

    public function dispatch(DomainEvent ...$events): void
    {
        foreach ($events as $event) {
            $this->events->dispatch($event);
        }
    }
}
