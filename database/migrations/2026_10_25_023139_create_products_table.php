<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')
                  ->constrained('categories')
                  ->cascadeOnUpdate()
                  ->restrictOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('name', 150);
            $table->decimal('price', 12, 2);
            $table->integer('stock')->default(0);
            $table->integer('minimum_stock')->default(5);
            $table->timestamps();
        });

        Schema::create('product_suppliers', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->decimal('supplier_price', 12, 2)->default(0);
            $table->primary(['product_id', 'supplier_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_suppliers');
        Schema::dropIfExists('products');
    }
};