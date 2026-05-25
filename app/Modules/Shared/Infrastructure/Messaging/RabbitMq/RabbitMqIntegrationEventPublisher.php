<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Messaging\RabbitMq;

use App\Modules\Shared\Application\Messaging\IntegrationEventPublisher;
use JsonException;
use PhpAmqpLib\Message\AMQPMessage;

final class RabbitMqIntegrationEventPublisher implements IntegrationEventPublisher
{
    public function __construct(
        private RabbitMqConnectionFactory $connections,
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @throws JsonException
     */
    public function publish(string $exchange, string $routingKey, array $payload): void
    {
        $connection = $this->connections->make();
        $channel = $connection->channel();

        $channel->exchange_declare(
            exchange: $exchange,
            type: 'topic',
            passive: false,
            durable: true,
            auto_delete: false,
        );

        $message = new AMQPMessage(
            body: json_encode($payload, JSON_THROW_ON_ERROR),
            properties: [
                'content_type' => 'application/json',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ],
        );

        $channel->basic_publish(
            msg: $message,
            exchange: $exchange,
            routing_key: $routingKey,
        );

        $channel->close();
        $connection->close();
    }
}
