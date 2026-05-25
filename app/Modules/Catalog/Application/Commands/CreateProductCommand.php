<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\Commands;

final class CreateProductCommand
{
    public function __construct(
        public string $name,
        public int $priceAmount,
        public string $currency,
        public int $stock,
    ) {

    }
}