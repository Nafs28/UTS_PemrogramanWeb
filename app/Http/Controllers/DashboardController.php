<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalSuppliers = Supplier::count();

        $salesToday = Sale::where('status', 'completed')->whereDate('sold_at', today())->sum('total');
        $salesThisMonth = Sale::where('status', 'completed')
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->sum('total');

        $purchasesThisMonth = Purchase::where('status', 'completed')
            ->whereMonth('purchased_at', now()->month)
            ->whereYear('purchased_at', now()->year)
            ->sum('total');

        $totalSalesCount = Sale::where('status', 'completed')->count();
        $totalPurchasesCount = Purchase::where('status', 'completed')->count();

        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock', 'asc')
            ->get();

        $recentSales = Sale::with('user')->latest('sold_at')->take(5)->get();
        $recentPurchases = Purchase::with(['supplier', 'user'])->latest('purchased_at')->take(5)->get();

        return view('dashboard', compact(
            'totalProducts',
            'totalCategories',
            'totalSuppliers',
            'salesToday',
            'salesThisMonth',
            'purchasesThisMonth',
            'totalSalesCount',
            'totalPurchasesCount',
            'lowStockProducts',
            'recentSales',
            'recentPurchases'
        ));
    }
}