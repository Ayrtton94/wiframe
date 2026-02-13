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
        Schema::create('transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('transfers')->cascadeOnDelete();
            $table->foreignId('store_id')->constrained('stores');
            $table->decimal('kilos_requested', 12, 3)->default(0);
            $table->decimal('metros_requested', 12, 3)->default(0);
            $table->decimal('kilos_shipped', 12, 3)->default(0);
            $table->decimal('metros_shipped', 12, 3)->default(0);
            $table->decimal('kilos_received', 12, 3)->default(0);
            $table->decimal('metros_received', 12, 3)->default(0);
            $table->timestamps();

            $table->unique(['transfer_id', 'store_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_items');
    }
};
