<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Infrastructure\Eventing\Listeners;

use App\Modules\Ordering\Domain\Events\OrderPaid;
use App\Modules\Ordering\Infrastructure\Jobs\PublishOrderPaidIntegrationEventJob;
use App\Modules\Ordering\Infrastructure\Jobs\RecordOrderPaidAuditLogJob;

final class DispatchOrderPaidJobs
{
    public function handle(OrderPaid $event): void
    {
        $paidAt = $event->occurredAt()->format(DATE_ATOM);

        RecordOrderPaidAuditLogJob::dispatch(
            orderId: $event->orderId()->value(),
            paidAt: $paidAt,
        )->afterCommit();

        PublishOrderPaidIntegrationEventJob::dispatch(
            orderId: $event->orderId()->value(),
            paidAt: $paidAt,
        )->afterCommit();
    }
}
