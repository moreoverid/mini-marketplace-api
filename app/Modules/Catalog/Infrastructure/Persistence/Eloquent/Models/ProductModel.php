<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

final class ProductModel extends Model
{
    protected $table = 'products';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'id',
        'name',
        'price_amount',
        'currency',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'price_amount' => 'integer',
            'stock' => 'integer',
        ];
    }
}
