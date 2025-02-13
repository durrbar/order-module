<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

// use Modules\Order\Database\Factories\OrderFactory;

class OrderItem extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'orderable_type',
        'orderable_id',
        'quantity',
        'price',
        'type', // e.g., 'physical', 'digital', 'service'
    ];

    // protected static function newFactory(): OrderFactory
    // {
    //     // return OrderFactory::new();
    // }

    // Relationships
    public function orderable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isPhysical(): bool
    {
        return $this->type === 'physical';
    }

    public function inDigital(): bool
    {
        return $this->type === 'digital';
    }

    /**
     * Check if the item is a service (e.g., tour, course).
     *
     * @return bool
     */
    public function isService(): bool
    {
        return $this->type === 'service';
    }
}
