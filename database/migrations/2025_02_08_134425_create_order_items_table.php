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
        Schema::create('old_order_items', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('old_order_id')->constrained()->cascadeOnDelete();
            $table->uuidMorphs('orderable');
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->string('type');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('old_order_items');
    }
};
