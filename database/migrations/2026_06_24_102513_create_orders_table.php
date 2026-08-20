<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('order_number')->unique();
            $table->string('idempotency_key', 64);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->string('discount_code')->nullable();
            $table->decimal('tax', 12, 2)->default(0);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('basket_id')->nullable()->constrained('baskets')->cascadeOnDelete();
            $table->decimal('total_price', 12, 2);
            $table->string('address', 500);
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'idempotency_key']);
            $table->index(['user_id', 'status']);
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
