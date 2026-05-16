<?php

declare(strict_types=1);

namespace App\Jobs\Ordering;

use App\Infrastructure\Persistence\Eloquent\Models\OrderPaymentLogModel;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

final class RecordOrderPaidAuditLogJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public function __construct(
        public readonly string $orderId,
        public readonly string $paidAt,
    ) {
        $this->onQueue('orders');
    }

    public function handle(): void
    {
        OrderPaymentLogModel::query()->updateOrCreate(
            [
                'order_id' => $this->orderId,
            ],
            [
                'paid_at' => CarbonImmutable::parse($this->paidAt),
            ],
        );
    }
}
