<?php

declare(strict_types=1);

namespace Modules\Order\Models;

use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Attributes\Unguarded;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Models\Scopes\OrderByCreatedAtDescScope;
use Modules\Coupon\Models\Coupon;
use Modules\Ecommerce\Models\Product;
use Modules\Ecommerce\Traits\TranslationTrait;
use Modules\Order\Policies\OrderPolicy;
use Modules\Payment\Models\PaymentIntent;
use Modules\Refund\Models\Refund;
use Modules\Review\Models\Review;
use Modules\User\Models\User;
use Modules\Vendor\Models\Shop;

#[ScopedBy([OrderByCreatedAtDescScope::class])]
#[Table('orders')]
#[Unguarded]
#[Hidden([
    'updated_at',
    'deleted_at',
])]
#[UsePolicy(OrderPolicy::class)]
class Order extends Model
{
    use HasUuids;
    use SoftDeletes;
    use TranslationTrait;

    protected $with = ['customer', 'products.variation_options'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot('order_quantity', 'unit_price', 'subtotal', 'variation_option_id')
            ->withTimestamps();
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class, 'coupon_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany('Modules\Ecommerce\Models\Order', 'parent_id', 'id');
    }

    public function parent_order(): HasOne
    {
        return $this->hasOne('Modules\Ecommerce\Models\Order', 'id', 'parent_id');
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class, 'order_id');
    }

    public function wallet_point(): HasOne
    {
        return $this->hasOne(OrderWalletPoint::class, 'order_id');
    }

    public function payment_intent(): HasMany
    {
        return $this->hasMany(PaymentIntent::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    protected function casts(): array
    {
        return [
            'shipping_address' => 'json',
            'billing_address' => 'json',
            'payment_intent_info' => 'json',
        ];
    }
}
