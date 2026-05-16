<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;

final class OrderPaymentLogModel extends Model
{
    protected $table = 'order_payment_logs';

    protected $fillable = [
        'order_id',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'immutable_datetime',
        ];
    }
}
