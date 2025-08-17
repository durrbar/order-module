<?php

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Coupon\Models\Coupon;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Traits\TranslationTrait;
use Modules\Payment\Models\PaymentIntent;
use Modules\Refund\Models\Refund;
use Modules\Review\Models\Review;
use Modules\User\Models\User;
use Modules\Vendor\Models\Shop;

class Order extends Model
{
    use HasUuids;
    use SoftDeletes;
    use TranslationTrait;

    protected $table = 'orders';

    public $guarded = [];

    protected $casts = [
        'shipping_address' => 'json',
        'billing_address' => 'json',
        'payment_intent_info' => 'json',
    ];

    protected $hidden = [
        //        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected static function boot()
    {
        parent::boot();
        // Order by created_at desc
        static::addGlobalScope('order', function (Builder $builder): void {
            $builder->orderBy('created_at', 'desc');
        });
    }

    protected $with = ['customer', 'products.variation_options'];

    public function products(): belongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('order_quantity', 'unit_price', 'subtotal', 'variation_option_id')
            ->withTimestamps();
    }

    public function coupon(): belongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function customer(): belongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    /**
     * @return HasMany
     */
    public function children()
    {
        return $this->hasMany('Modules\Ecommerce\Models\Order', 'parent_id', 'id');
    }

    /**
     * @return HasOne
     */
    public function parent_order()
    {
        return $this->hasOne('Modules\Ecommerce\Models\Order', 'id', 'parent_id');
    }

    /**
     * @return HasOne
     */
    public function refund()
    {
        return $this->hasOne(Refund::class, 'order_id');
    }

    /**
     * @return HasOne
     */
    public function wallet_point()
    {
        return $this->hasOne(OrderWalletPoint::class, 'order_id');
    }

    /**
     * @return HasMany
     */
    public function payment_intent()
    {
        return $this->hasMany(PaymentIntent::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
