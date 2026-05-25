<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Infrastructure\Jobs;

use App\Modules\Shared\Application\Messaging\IntegrationEventPublisher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

final class PublishOrderPaidIntegrationEventJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 30;

    public readonly string $eventId;

    public function __construct(
        public readonly string $orderId,
        public readonly string $paidAt,
        ?string $eventId = null,
    ) {
        $this->eventId = $eventId ?? (string) Str::uuid();

        $this->onQueue('orders');
    }

    public function handle(IntegrationEventPublisher $publisher): void
    {
        $publisher->publish(
            exchange: (string) config('rabbitmq.exchange'),
            routingKey: 'order.paid',
            payload: [
                'event_id' => $this->eventId,
                'event_type' => 'order.paid',
                'occurred_at' => $this->paidAt,
                'data' => [
                    'order_id' => $this->orderId,
                ],
            ],
        );
    }
}
