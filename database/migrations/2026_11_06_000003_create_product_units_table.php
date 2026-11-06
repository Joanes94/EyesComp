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
        Schema::create('product_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('unit_name', 50); // ex: Carton, Pack, Sachet
            $table->unsignedInteger('conversion_factor')->default(1); // ex: 24 (1 carton = 24 unités de base)
            $table->unsignedBigInteger('sale_price'); // Prix spécifique pour cette unité en XOF
            $table->string('barcode')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'unit_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_units');
    }
};
