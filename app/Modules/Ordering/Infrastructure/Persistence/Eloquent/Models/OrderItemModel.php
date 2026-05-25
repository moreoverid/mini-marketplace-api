<?php

declare(strict_types=1);

namespace App\Modules\Ordering\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class OrderItemModel extends Model
{
    protected $table = 'order_items';

    protected $fillable = [
        'order_id',
        'product_id',
        'unit_price_amount',
        'currency',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'unit_price_amount' => 'integer',
            'quantity' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(OrderModel::class, 'order_id', 'id');
    }
}
