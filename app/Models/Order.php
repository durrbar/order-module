<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Modules\Address\Models\Address;
use Modules\Order\Observers\OrderObserver;

// use Modules\Order\Database\Factories\OrderFactory;

#[ObservedBy([OrderObserver::class])]
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

    /**
     * Get all physical items in the order.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPhysicalItems()
    {
        return $this->items()->where('type', 'physical')->get();
    }

    /**
     * Get all digital items in the order.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getDigitalItems()
    {
        return $this->items()->where('type', 'digital')->get();
    }

    /**
     * Get all service items in the order.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getServiceItems()
    {
        return $this->items()->where('type', 'service')->get();
    }

    /**
     * Check if the order contains any physical items.
     *
     * @return bool
     */
    public function hasPhysicalItems(): bool
    {
        return $this->items()->where('type', 'physical')->exists();
    }

    /**
     * MorphToMany relationship to addresses.
     */
    public function addresses()
    {
        return $this->morphToMany(Address::class, 'addressable')
                    ->withPivot('type'); // Include the 'type' column from the pivot table
    }

    /**
     * Get the billing address for the order.
     */
    public function billingAddress()
    {
        return $this->addresses()->wherePivot('type', 'billing')->first();
    }

    /**
     * Get the shipping address for the order.
     */
    public function shippingAddress()
    {
        return $this->addresses()->wherePivot('type', 'shipping')->first();
    }

    /**
     * Check if the order has a billing address.
     */
    public function hasBillingAddress()
    {
        return $this->addresses()->wherePivot('type', 'billing')->exists();
    }

    /**
     * Check if the order has a shipping address.
     */
    public function hasShippingAddress()
    {
        return $this->addresses()->wherePivot('type', 'shipping')->exists();
    }
}
