<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. users (id, name, email unique, password, role)
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'staff'])->default('staff')->index();
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. categories (id, name unique)
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->timestamps();
        });

        // 3. suppliers (id, name, phone, email)
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('phone', 50);
            $table->string('email', 100)->nullable();
            $table->timestamps();
        });

        // 4. products (id, category_id FK, sku unique, name, purchase_price, selling_price, stock, minimum_stock)
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('sku', 50)->unique();
            $table->string('name', 150)->index();
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->integer('stock')->default(0);
            $table->integer('minimum_stock')->default(5);
            $table->timestamps();
        });

        // 5. purchases (id, supplier_id FK, user_id FK, purchase_number unique, total, status, purchased_at)
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('supplier_id')->constrained('suppliers')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('purchase_number', 50)->unique();
            $table->decimal('total', 14, 2)->default(0);
            $table->enum('status', ['completed', 'cancelled'])->default('completed')->index();
            $table->timestamp('purchased_at')->index();
            $table->timestamps();
        });

        // 6. purchase_items (id, purchase_id FK, product_id FK, quantity, unit_cost, subtotal)
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });

        // 7. sales (id, user_id FK, sale_number unique, total, status, sold_at)
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->string('sale_number', 50)->unique();
            $table->decimal('total', 14, 2)->default(0);
            $table->enum('status', ['completed', 'cancelled'])->default('completed')->index();
            $table->timestamp('sold_at')->index();
            $table->timestamps();
        });

        // 8. sale_items (id, sale_id FK, product_id FK, quantity, unit_price, subtotal)
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('subtotal', 14, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
        Schema::dropIfExists('products');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('users');
    }
};