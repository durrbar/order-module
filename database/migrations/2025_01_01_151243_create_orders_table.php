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
        Schema::create('old_orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('order_number')->unique();
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'canceled', 'refunded'])->default('pending');
            $table->foreignUuid('customer_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_amount', 10, 2);

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
