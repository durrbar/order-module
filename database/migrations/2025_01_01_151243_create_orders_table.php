<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Order\Enums\OrderLegacyStatus;

return new class() extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('old_orders', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            $table->string('order_number')->unique();
            $table->enum(
                'status',
                OrderLegacyStatus::cases()
            )->default(OrderLegacyStatus::Pending->value);
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
        Schema::dropIfExists('old_orders');
    }
};
