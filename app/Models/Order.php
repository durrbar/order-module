<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

// use Modules\Order\Database\Factories\OrderFactory;

class Order extends Model
{
    use HasFactory;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [];

    // protected static function newFactory(): OrderFactory
    // {
    //     // return OrderFactory::new();
    // }

    // Relationships
    public function customer(): BelongsTo
    {
        return $this->belongsTo(config('order.customer.model'), 'customer_id');
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(config('order.invoice.model'), 'order_id', 'id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(config('order.payment.model'), 'order_id', 'id');
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(config('order.delivery.model'), 'order_id', 'id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'id');
    }

    public function history(): HasOne
    {
        return $this->hasOne(OrderHistory::class, 'order_id', 'id');
    }
}
