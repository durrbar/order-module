<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Order\Enums\OrderStatus;
use Modules\Payment\Enums\PaymentStatus;

return new class () extends Migration {
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
            // $table->uuid('customer_id')->nullable();
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
            $table->enum('order_status', OrderStatus::getValues())->default(OrderStatus::PENDING);
            $table->enum('payment_status', PaymentStatus::getValues())->default(PaymentStatus::PENDING);
            $table->softDeletes();
            $table->timestamps();
            $table->foreignUuid('customer_id')->references('id')->on('users')->nullable();
        });

        Schema::create('order_product', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            // $table->uuid('order_id');
            // $table->uuid('product_id');
            $table->string('order_quantity');
            $table->double('unit_price');
            $table->double('subtotal');
            $table->softDeletes();
            $table->timestamps();
            $table->foreignUuid('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreignUuid('product_id')->references('id')->on('products')->onDelete('cascade');
        });

        Schema::create('order_wallet_points', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->double('amount')->nullable();
            $table->uuid('order_id')->nullable();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
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
            $table->uuid('digital_file_id')->nullable();
            $table->text('payload')->nullable();
            $table->foreign('digital_file_id')->references('id')->on('digital_files')->onDelete('cascade');
            $table->uuid('user_id');
            $table->timestamps();
        });

        Schema::create('ordered_files', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('purchase_key');
            $table->uuid('digital_file_id');
            $table->foreign('digital_file_id')->references('id')->on('digital_files')->onDelete('cascade');
            $table->string('tracking_number')->nullable();
            $table->foreign('tracking_number')->references('tracking_number')->on('orders')->onDelete('cascade');
            $table->uuid('customer_id');
            $table->foreign('customer_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('orders');
        Schema::dropIfExists('order_product');
        Schema::dropIfExists('order_wallet_points');
        Schema::dropIfExists('ordered_files');
        Schema::dropIfExists('digital_files');
        Schema::dropIfExists('download_tokens');
    }
};
