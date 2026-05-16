<?php

declare(strict_types=1);

namespace Tests\Feature\Jobs\Ordering;

use App\Infrastructure\Persistence\Eloquent\Models\OrderModel;
use App\Jobs\Ordering\RecordOrderPaidAuditLogJob;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

final class RecordOrderPaidAuditLogJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_records_order_paid_audit_log(): void
    {
        $orderId = (string) Str::uuid();

        OrderModel::query()->create([
            'id' => $orderId,
            'status' => 'paid',
        ]);

        $job = new RecordOrderPaidAuditLogJob(
            orderId: $orderId,
            paidAt: '2026-05-16T10:00:00+00:00',
        );

        $job->handle();

        $this->assertDatabaseHas('order_payment_logs', [
            'order_id' => $orderId,
        ]);
    }

    public function test_it_is_idempotent(): void
    {
        $orderId = (string) Str::uuid();

        OrderModel::query()->create([
            'id' => $orderId,
            'status' => 'paid',
        ]);

        $job = new RecordOrderPaidAuditLogJob(
            orderId: $orderId,
            paidAt: '2026-05-16T10:00:00+00:00',
        );

        $job->handle();
        $job->handle();

        $this->assertDatabaseCount('order_payment_logs', 1);
    }
}
