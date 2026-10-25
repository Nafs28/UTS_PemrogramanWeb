<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with('user')->withCount('items');

        if ($request->filled('search')) {
            $query->where('sale_number', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('sold_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('sold_at', '<=', $request->end_date);
        }

        $sales = $query->latest('sold_at')->paginate(10)->withQueryString();
        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products = Product::orderBy('name')->get();
        return view('sales.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
        ]);

        // Persis seperti contoh kode Slide 16 PPT UTS
        DB::transaction(function () use ($request) {
            $sale = Sale::create([
                'user_id' => auth()->id(),
                'sale_number' => $this->generateSaleNumber(),
                'total' => 0,
                'status' => 'completed',
                'sold_at' => now(),
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];

                if ($qty < 1 || $product->stock < $qty) {
                    throw ValidationException::withMessages([
                        'items' => "Transaksi ditolak! Stok '{$product->name}' tidak mencukupi (Tersedia: {$product->stock}, Diminta: {$qty}). Seluruh transaksi di-rollback.",
                    ]);
                }

                $subtotal = $product->selling_price * $qty;

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $product->selling_price,
                    'subtotal' => $subtotal,
                ]);

                $product->decrement('stock', $qty);
                $total += $subtotal;
            }

            $sale->update(['total' => $total]);
        });

        return redirect()->route('sales.index')->with('success', 'Penjualan berhasil disimpan dan stok produk otomatis berkurang.');
    }

    private function generateSaleNumber(): string
    {
        return 'INV-' . now()->format('YmdHis') . '-' . rand(100, 999);
    }

    public function show(Sale $sale)
    {
        $sale->load(['user', 'items.product']);
        return view('sales.show', compact('sale'));
    }

    public function cancel(Sale $sale)
    {
        Gate::authorize('admin-only');
        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Penjualan ini sudah dibatalkan sebelumnya.');
        }

        DB::transaction(function () use ($sale) {
            $sale->load('items');
            foreach ($sale->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item->product_id);
                $product->increment('stock', $item->quantity);
            }
            $sale->update(['status' => 'cancelled']);
        });

        return redirect()->route('sales.show', $sale)->with('success', 'Transaksi penjualan dibatalkan dan stok produk telah dikembalikan secara konsisten.');
    }
}