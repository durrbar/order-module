<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Order\Enums\OrderStatus;
use Modules\Payment\Enums\PaymentStatus;

return new class() extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('tracking_number')->unique();
            $table->foreignUuid('customer_id')->nullable()->constrained('users');
            $table->string('customer_contact');
            $table->string('customer_name')->nullable();
            $table->double('amount');
            $table->double('sales_tax')->nullable();
            $table->double('paid_total')->nullable();
            $table->double('total')->nullable();
            $table->uuid('coupon_id')->nullable();
            $table->double('discount')->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('altered_payment_gateway')->nullable();
            $table->json('shipping_address')->nullable();
            $table->json('billing_address')->nullable();
            $table->uuid('logistics_provider')->nullable();
            $table->double('delivery_fee')->nullable();
            $table->string('delivery_time')->nullable();
            $table->enum(
                'order_status',
                OrderStatus::cases()
            )->default(OrderStatus::Pending->value);
            $table->enum(
                'payment_status',
                PaymentStatus::cases()
            )->default(PaymentStatus::Pending->value);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_product', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->string('order_quantity');
            $table->double('unit_price');
            $table->double('subtotal');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('order_wallet_points', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->double('amount')->nullable();
            $table->foreignUuid('order_id')->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('digital_files', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('attachment_id');
            $table->string('url');
            $table->string('file_name');
            $table->string('fileable_type');
            $table->uuid('fileable_id');
            $table->timestamps();
        });
        Schema::create('download_tokens', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('token');
            $table->foreignUuid('digital_file_id')->nullable()->constrained()->cascadeOnDelete();
            $table->text('payload')->nullable();
            $table->uuid('user_id');
            $table->timestamps();
        });

        Schema::create('ordered_files', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('purchase_key');
            $table->foreignUuid('digital_file_id')->constrained()->cascadeOnDelete();
            $table->string('tracking_number')->nullable()->constrained('orders', 'tracking_number')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::table('orders', function (Blueprint $table): void {
            $table->string('language')->after('total')->default(DEFAULT_LANGUAGE);
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_product');
        Schema::dropIfExists('order_wallet_points');
        Schema::dropIfExists('ordered_files');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('download_tokens');
        Schema::dropIfExists('digital_files');
    }
};
