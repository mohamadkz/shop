<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Catalog's own idempotency ledger for the OrderPlaced saga step.
        // order_id is a plain FK (ID) into Order — no cross-domain
        // constraint, matching the "IDs only across aggregates" rule.
        Schema::create('catalog_stock_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->unique();
            $table->json('reserved_lines');
            $table->timestamp('restocked_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_stock_reservations');
    }
};
