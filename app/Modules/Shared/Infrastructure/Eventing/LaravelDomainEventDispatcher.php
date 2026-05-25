<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Eventing;

use App\Modules\Shared\Application\Eventing\DomainEventDispatcher;
use App\Modules\Shared\Domain\Events\DomainEvent;
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
