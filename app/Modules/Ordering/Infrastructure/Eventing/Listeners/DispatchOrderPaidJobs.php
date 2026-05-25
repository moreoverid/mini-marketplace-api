<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Infrastructure\Eventing\Listeners;

use App\Modules\Ordering\Domain\Events\OrderPaid;
use App\Modules\Ordering\Infrastructure\Jobs\RecordOrderPaidAuditLogJob;

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
