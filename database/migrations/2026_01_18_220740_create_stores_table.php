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
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->string('code_product')->unique();
            $table->string('name_product');
            $table->string('fabric_type');
            $table->string('color');
           ///Proveedor y Stock
            $table->string('proveedor');
            $table->string('kilos');
            $table->string('metros');
            $table->integer('minimum_stock');
            ///Precios
            $table->decimal('price');
            $table->decimal('public_price');
            $table->decimal('wholesale_price');
            $table->decimal('price_roll');
            $table->decimal('special_price');            
            ///ubicacion
            $table->string('location')->nullable();
            $table->string('description')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stores');
    }
};
