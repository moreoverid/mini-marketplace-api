<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Application\Exceptions;

use RuntimeException;

final class OrderNotFoundException extends RuntimeException
{
    public static function forId(string $id): self
    {
        return new self("Order [{$id}] not found.");
    }
}
