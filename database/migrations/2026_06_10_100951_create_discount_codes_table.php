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
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // مثلا: OFF20
            $table->decimal('percent', 5, 2); // درصد تخفیف
            $table->decimal('max_discount', 10, 2)->nullable(); // سقف مبلغ تخفیف
            $table->timestamp('expired_at')->nullable(); // تاریخ انقضا
            $table->integer('usage_limit')->default(1); // تعداد دفعات مجاز استفاده
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
