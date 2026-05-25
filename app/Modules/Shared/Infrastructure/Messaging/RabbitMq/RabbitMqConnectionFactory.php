<?php

declare(strict_types=1);

namespace App\Modules\Shared\Infrastructure\Messaging\RabbitMq;

use PhpAmqpLib\Connection\AMQPStreamConnection;

final class RabbitMqConnectionFactory
{
    public function make(): AMQPStreamConnection
    {
        return new AMQPStreamConnection(
            host: (string) config('rabbitmq.host'),
            port: (int) config('rabbitmq.port'),
            user: (string) config('rabbitmq.user'),
            password: (string) config('rabbitmq.password'),
            vhost: (string) config('rabbitmq.vhost'),
        );
    }
}
