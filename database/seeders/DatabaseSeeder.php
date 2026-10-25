<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Owner Kopi Nusantara',
            'email' => 'admin@kopinusantara.test',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        $staff = User::create([
            'name' => 'Staf Kasir & Gudang',
            'email' => 'staff@kopinusantara.test',
            'password' => Hash::make('password'),
            'role' => 'staff',
        ]);

        $catArabika = Category::create(['name' => 'Biji Kopi Arabika']);
        $catRobusta = Category::create(['name' => 'Biji Kopi Robusta']);
        $catSyrup   = Category::create(['name' => 'Sirup & Bahan Campuran']);
        $catAlat    = Category::create(['name' => 'Alat Seduh Manual']);

        $supGayo = Supplier::create([
            'name' => 'Koperasi Petani Kopi Gayo Aceh',
            'phone' => '08116700123',
            'email' => 'order@kopigayo.co.id',
        ]);

        $supRoastery = Supplier::create([
            'name' => 'PT Nusantara Roastery Supply',
            'phone' => '08123456789',
            'email' => 'supply@nusantararoastery.id',
        ]);

        $p1 = Product::create([
            'category_id' => $catArabika->id,
            'sku' => 'KOPI-ARB-001',
            'name' => 'Arabika Aceh Gayo Semi-Wash 250g',
            'purchase_price' => 65000,
            'selling_price' => 95000,
            'stock' => 25,
            'minimum_stock' => 10,
        ]);

        $p2 = Product::create([
            'category_id' => $catArabika->id,
            'sku' => 'KOPI-ARB-002',
            'name' => 'Arabika Toraja Sapan Full-Wash 250g',
            'purchase_price' => 70000,
            'selling_price' => 105000,
            'stock' => 4, // Low stock!
            'minimum_stock' => 8,
        ]);

        $p3 = Product::create([
            'category_id' => $catRobusta->id,
            'sku' => 'KOPI-ROB-001',
            'name' => 'Robusta Dampit Fine Grade 500g',
            'purchase_price' => 55000,
            'selling_price' => 80000,
            'stock' => 30,
            'minimum_stock' => 10,
        ]);

        $p4 = Product::create([
            'category_id' => $catSyrup->id,
            'sku' => 'SYR-VAN-001',
            'name' => 'Sirup Vanilla Gourmet Barista 750ml',
            'purchase_price' => 90000,
            'selling_price' => 135000,
            'stock' => 3, // Low stock!
            'minimum_stock' => 5,
        ]);

        $p5 = Product::create([
            'category_id' => $catAlat->id,
            'sku' => 'EQP-V60-001',
            'name' => 'Dripper Hario V60 Ceramic 02',
            'purchase_price' => 120000,
            'selling_price' => 185000,
            'stock' => 12,
            'minimum_stock' => 5,
        ]);

        // Sample Purchase (Stock-In)
        $po = Purchase::create([
            'supplier_id' => $supGayo->id,
            'user_id' => $admin->id,
            'purchase_number' => 'PO-20261025-001',
            'total' => 1300000,
            'status' => 'completed',
            'purchased_at' => now()->subDays(1),
        ]);
        $po->items()->create([
            'product_id' => $p1->id,
            'quantity' => 20,
            'unit_cost' => 65000,
            'subtotal' => 1300000,
        ]);

        // Sample Sale (Stock-Out)
        $sale = Sale::create([
            'user_id' => $staff->id,
            'sale_number' => 'INV-20261025-001',
            'total' => 350000,
            'status' => 'completed',
            'sold_at' => now(),
        ]);
        $sale->items()->create([
            'product_id' => $p1->id,
            'quantity' => 2,
            'unit_price' => 95000,
            'subtotal' => 190000,
        ]);
        $sale->items()->create([
            'product_id' => $p3->id,
            'quantity' => 2,
            'unit_price' => 80000,
            'subtotal' => 160000,
        ]);
    }
}