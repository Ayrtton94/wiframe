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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            //datos del proveedor
            $table->string('ruc');
            $table->string('company_name');
            $table->string('category');
            $table->string('phone');
            $table->string('email');
            //
            $table->string('name');
            $table->string('address');
            $table->string('city');
            $table->string('country');
            $table->string('account');
            $table->string('cod_swift');
            //
            $table->string('bank_name');
            $table->string('bank_address');
            $table->string('bank_city');
            $table->string('bank_country');
            $table->string('bank_cod_swift');
            //
            $table->string('bank_name2')->nullable();
            $table->string('bank_address2')->nullable();
            $table->string('bank_cod_swift2')->nullable();
            //
            $table->string('others');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
