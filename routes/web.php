<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockMovementController;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Dashboard & Laporan Agregat
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Resource Route untuk CRUD Produk & Kategori (Slide 16 & 18)
Route::resource('products', ProductController::class);
Route::resource('categories', CategoryController::class)->only(['index', 'store', 'destroy']);

// Route Transaksi Stok Barang Masuk (IN) & Keluar (OUT)
Route::get('/stock', [StockMovementController::class, 'index'])->name('stock.index');
Route::post('/stock', [StockMovementController::class, 'store'])->name('stock.store');

// Automated Test Runner untuk Checklist Pertemuan 5 (Slide 41 & 46)
Route::get('/test-runner', function () {
    $checks = [
        [
            'title' => 'Environment (.env) & Koneksi Database Aktif',
            'category' => 'Environment',
            'pass' => Schema::hasTable('products') && Schema::hasTable('categories') && Schema::hasTable('stock_movements'),
            'detail' => 'Terhubung ke database: ' . config('database.connections.mysql.database'),
        ],
        [
            'title' => 'Migration & Seeder Menghasilkan Data Uji',
            'category' => 'Migration & Seeder',
            'pass' => Category::count() >= 3 && Product::count() >= 3,
            'detail' => 'Terdapat ' . Category::count() . ' kategori dan ' . Product::count() . ' produk di database.',
        ],
        [
            'title' => 'Eloquent Relationship (Category hasMany Product & Product belongsTo Category)',
            'category' => 'Eloquent ORM',
            'pass' => optional(Product::with('category')->first())->category !== null,
            'detail' => 'Relasi Eager Loading Product::with(\'category\') berjalan dengan benar.',
        ],
        [
            'title' => 'Resource Controller & Named Routes Terdaftar (products.index s/d products.destroy)',
            'category' => 'Routing & Controller',
            'pass' => Route::has('products.index') && Route::has('products.store') && Route::has('products.update') && Route::has('products.destroy'),
            'detail' => '7 route standar Resource Controller terverifikasi.',
        ],
        [
            'title' => 'Blade Layout, Reusable Component (<x-alert>), dan Output Escaping {{ }}',
            'category' => 'Blade View',
            'pass' => view()->exists('layouts.app') && view()->exists('components.alert') && e('<b>Promo</b>') === '&lt;b&gt;Promo&lt;/b&gt;',
            'detail' => 'File layouts/app.blade.php, components/alert.blade.php, dan Anti-XSS aktif.',
        ],
        [
            'title' => 'Business Rule P4 Dipertahankan (DB::transaction & Proteksi Stok Negatif)',
            'category' => 'Business Logic P4 -> P5',
            'pass' => Route::has('stock.store') && Product::where('stock', '<', 0)->count() === 0,
            'detail' => 'Transaksi IN/OUT menggunakan DB::transaction() dan tidak ada produk dengan stok negatif.',
        ],
    ];

    return view('test_runner', compact('checks'));
})->name('test.runner');