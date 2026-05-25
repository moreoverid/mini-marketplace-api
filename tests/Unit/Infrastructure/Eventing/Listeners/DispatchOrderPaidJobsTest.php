<?php

declare(strict_types=1);

namespace Tests\Unit\Infrastructure\Eventing\Listeners;

use App\Modules\Ordering\Domain\Events\OrderPaid;
use App\Modules\Ordering\Domain\ValueObjects\OrderId;
use App\Modules\Ordering\Infrastructure\Eventing\Listeners\DispatchOrderPaidJobs;
use App\Modules\Ordering\Infrastructure\Jobs\RecordOrderPaidAuditLogJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class DispatchOrderPaidJobsTest extends TestCase
{
    public function test_it_dispatches_record_order_paid_audit_log_job(): void
    {
        Queue::fake();

        $orderId = OrderId::generate();

        $listener = new DispatchOrderPaidJobs();

        $listener->handle(new OrderPaid($orderId));

        Queue::assertPushed(
            RecordOrderPaidAuditLogJob::class,
            static fn (RecordOrderPaidAuditLogJob $job): bool => $job->orderId === $orderId->value(),
        );
    }
}
