<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 5 Widget Ringkasan Inventory (Kesinambungan P4 Slide 29)
        $totalProducts = Product::count();
        $inventoryValue = Product::sum(DB::raw('stock * price'));
        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->get();
        $totalIn = StockMovement::where('type', 'IN')->sum('quantity');
        $totalOut = StockMovement::where('type', 'OUT')->sum('quantity');

        // Laporan Agregat per Kategori menggunakan Eloquent withCount, withSum, withAvg
        $categoryReport = Category::withCount('products')
            ->withSum('products', 'stock')
            ->withAvg('products', 'price')
            ->get();

        return view('dashboard', compact(
            'totalProducts',
            'inventoryValue',
            'lowStockProducts',
            'totalIn',
            'totalOut',
            'categoryReport'
        ));
    }
}