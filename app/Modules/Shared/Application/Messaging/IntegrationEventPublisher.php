<?php

declare(strict_types=1);

namespace App\Modules\Shared\Application\Messaging;

interface IntegrationEventPublisher
{
    /**
     * @param array<string, mixed> $payload
     */
    public function publish(string $exchange, string $routingKey, array $payload): void;
}
