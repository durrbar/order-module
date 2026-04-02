<?php

declare(strict_types=1);

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// use Modules\Order\Database\Factories\OrderFactory;

#[Fillable([])]
class OrderHistory extends Model
{
    use HasFactory;
    use HasUuids;

    // Relationships
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }

    // protected static function newFactory(): OrderFactory
    // {
    //     // return OrderFactory::new();
    // }
    protected function casts(): array
    {
        return [
            'order_time' => 'datetime',
            'payment_time' => 'datetime',
            'delivery_time' => 'datetime',
            'completion_time' => 'datetime',
            'timeline' => 'array', // Cast JSON timeline to an array
        ];
    }
}
