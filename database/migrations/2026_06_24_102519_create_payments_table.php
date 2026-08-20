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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('order_id')->constrained('orders')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            //Add User, gateway, authority, ref_id, tracking_code, card_pan
            
            $table->string('authority', 64)->nullable()->unique();
            $table->string('ref_id', 64)->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('card_pan', 32)->nullable();

            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 30)->default('zarinpal');
            $table->string('transaction_id')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
