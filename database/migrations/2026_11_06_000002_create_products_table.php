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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->string('base_unit', 50)->default('pièce');
            $table->unsignedBigInteger('purchase_price')->default(0); // Coût moyen d'achat unitaire en XOF (RG-02 / RG-05)
            $table->unsignedBigInteger('sale_price')->default(0); // Prix de vente standard en XOF (RG-05)
            $table->unsignedInteger('alert_threshold')->default(5); // Seuil alerte stock bas (F-STK-04)
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('active'); // active, archived
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'barcode']);
            $table->index(['tenant_id', 'sku']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
