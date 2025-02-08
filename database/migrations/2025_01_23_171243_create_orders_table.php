<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('order_number')->unique();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'canceled'])->default('pending');
            $table->foreignUuid('customer_id')->constrained('users')->cascadeOnDelete(); // Link to a user or customer
            $table->decimal('total_amount', 10, 2);
            $table->foreignUuid('invoice_id')->constrained()->cascadeOnDelete()->nullable(); // Link to Invoice
            $table->foreignUuid('payment_id')->constrained()->cascadeOnDelete()->nullable(); // Link to Payment
            $table->foreignUuid('delivery_id')->constrained()->cascadeOnDelete()->nullable(); // Link to Delivery

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
