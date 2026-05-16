<?php

declare(strict_types=1);

namespace App\Application\Ordering\Exceptions;

use RuntimeException;

final class OrderNotFoundException extends RuntimeException
{
    public static function forId(string $id): self
    {
        return new self("Order [{$id}] not found.");
    }
}
