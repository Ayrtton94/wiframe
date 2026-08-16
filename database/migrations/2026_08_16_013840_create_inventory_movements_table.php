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
        Schema::create('inventory_movements', function (Blueprint $table) {
               $table->id();

    $table->unsignedBigInteger('warehouse_id');
    $table->unsignedBigInteger('store_id');

    $table->string('type', 30);
    $table->string('unit', 20);

    $table->decimal('quantity', 12, 3)->default(0);

    $table->string('reason', 50)->nullable();

    $table->string('reference_type', 50)->nullable();
    $table->unsignedBigInteger('reference_id')->nullable();
    $table->string('reference_code')->nullable();

    $table->unsignedBigInteger('user_id')->nullable();

    $table->timestamps();

    // Relaciones
    $table->foreign('warehouse_id')
        ->references('id')
        ->on('warehouses')
        ->onDelete('cascade');

    $table->foreign('store_id')
        ->references('id')
        ->on('stores')
        ->onDelete('cascade');

    $table->foreign('user_id')
        ->references('id')
        ->on('users')
        ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
