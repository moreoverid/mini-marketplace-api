<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Ordering\Infrastructure\Jobs;

use App\Modules\Ordering\Infrastructure\Jobs\PublishOrderPaidIntegrationEventJob;
use App\Modules\Shared\Application\Messaging\IntegrationEventPublisher;
use Tests\TestCase;

final class PublishOrderPaidIntegrationEventJobTest extends TestCase
{
    public function test_it_publishes_order_paid_integration_event(): void
    {
        config([
            'rabbitmq.exchange' => 'marketplace.events',
        ]);

        $publisher = new SpyIntegrationEventPublisher();

        $job = new PublishOrderPaidIntegrationEventJob(
            orderId: '11111111-1111-1111-1111-111111111111',
            paidAt: '2026-05-25T10:00:00+00:00',
            eventId: 'event-1',
        );

        $job->handle($publisher);

        $this->assertSame('marketplace.events', $publisher->exchange);
        $this->assertSame('order.paid', $publisher->routingKey);
        $this->assertSame('event-1', $publisher->payload['event_id']);
        $this->assertSame('order.paid', $publisher->payload['event_type']);
        $this->assertSame('2026-05-25T10:00:00+00:00', $publisher->payload['occurred_at']);
        $this->assertSame(
            '11111111-1111-1111-1111-111111111111',
            $publisher->payload['data']['order_id'],
        );
    }
}

final class SpyIntegrationEventPublisher implements IntegrationEventPublisher
{
    public ?string $exchange = null;

    public ?string $routingKey = null;

    /**
     * @var array<string, mixed>
     */
    public array $payload = [];

    public function publish(string $exchange, string $routingKey, array $payload): void
    {
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->payload = $payload;
    }
}
