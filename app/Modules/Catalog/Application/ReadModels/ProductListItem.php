<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Application\ReadModels;

final readonly class ProductListItem
{
    public function __construct(
        public string $id,
        public string $name,
        public int $priceAmount,
        public string $currency,
        public int $stock,
        public ?string $createdAt,
    ) {
    }
}
