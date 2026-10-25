<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat User Admin & Staff (Kesinambungan P4)
        $admin = User::create([
            'name' => 'Budi Santoso (Admin Gudang)',
            'email' => 'admin@kopinusantara.test',
            'password' => Hash::make('admin123'),
        ]);

        // 2. Buat Kategori Inti Toko Kopi Nusantara (P3 & P4)
        $catKopi = Category::create(['name' => 'Biji Kopi']);
        $catAlat = Category::create(['name' => 'Alat Seduh']);
        $catAksesoris = Category::create(['name' => 'Aksesoris Barista']);

        // 3. Buat Supplier
        $supGayo = Supplier::create(['name' => 'Koperasi Petani Kopi Gayo', 'contact' => '0811-6000-111']);
        $supBrew = Supplier::create(['name' => 'PT Nusantara Brewing Gear', 'contact' => '021-555-8899']);

        // 4. Buat Produk Inti (P3 & P4)
        $p1 = Product::create([
            'category_id' => $catKopi->id,
            'sku' => 'KOP-001',
            'name' => 'Kopi Arabika Aceh Gayo 250g',
            'price' => 85000,
            'stock' => 15,
            'minimum_stock' => 5,
        ]);
        $p1->suppliers()->attach($supGayo->id, ['supplier_price' => 68000]);

        $p2 = Product::create([
            'category_id' => $catAlat->id,
            'sku' => 'ALT-001',
            'name' => 'V60 Dripper Ceramic 02',
            'price' => 120000,
            'stock' => 4, // Stok rendah (<= minimum_stock)
            'minimum_stock' => 5,
        ]);
        $p2->suppliers()->attach($supBrew->id, ['supplier_price' => 95000]);

        $p3 = Product::create([
            'category_id' => $catAksesoris->id,
            'sku' => 'AKS-001',
            'name' => 'Paper Filter V60 Isi 100',
            'price' => 45000,
            'stock' => 25,
            'minimum_stock' => 10,
        ]);
        $p3->suppliers()->attach($supBrew->id, ['supplier_price' => 32000]);

        // 5. Catat Histori Stok Awal
        StockMovement::create([
            'product_id' => $p1->id,
            'user_id' => $admin->id,
            'type' => 'IN',
            'quantity' => 15,
            'notes' => 'Stok awal gudang dari petani',
        ]);
        StockMovement::create([
            'product_id' => $p2->id,
            'user_id' => $admin->id,
            'type' => 'IN',
            'quantity' => 4,
            'notes' => 'Stok awal alat seduh',
        ]);

        // 6. Tambahan Data Dummy dari Factory (Slide 29 & 30) agar Pagination terlihat
        Product::factory(8)->create();
    }
}