<?php

declare(strict_types=1);

namespace App\Infrastructure\Eventing\Listeners;

use App\Domain\Ordering\Events\OrderPaid;
use App\Jobs\Ordering\RecordOrderPaidAuditLogJob;

final class DispatchOrderPaidJobs
{
    public function handle(OrderPaid $event): void
    {
        RecordOrderPaidAuditLogJob::dispatch(
            orderId: $event->orderId()->value(),
            paidAt: $event->occurredAt()->format(DATE_ATOM),
        )->afterCommit();
    }
}
