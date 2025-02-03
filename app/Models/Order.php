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
        return $this->belongsTo(config('order.customer.model'));
    }
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(config('order.invoice.model'), 'invoice_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(config('order.payment.model'), 'payment_id');
    }

    public function delivery(): HasOne
    {
        return $this->hasOne(config('order.delivery.model'), 'delivery_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class); // Assuming an OrderItem model exists
    }

    public function history(): HasOne
    {
        return $this->hasOne(OrderHistory::class, 'order_id', 'id');
    }
}
