<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockMovementController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('name')->get();
        $movements = StockMovement::with('product')->latest()->paginate(10);

        return view('stock.index', compact('products', 'movements'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type'       => ['required', 'in:IN,OUT'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'notes'      => ['nullable', 'string', 'max:255'],
        ]);

        try {
            // Database Transaction + Row Locking (Business Rule P4 yang dipertahankan di P5)
            DB::transaction(function () use ($validated) {
                $product = Product::where('id', $validated['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($validated['type'] === 'OUT' && $validated['quantity'] > $product->stock) {
                    throw new Exception(
                        "Stok tidak mencukupi! Stok '{$product->name}' saat ini hanya {$product->stock} unit, tidak bisa dikeluarkan {$validated['quantity']} unit."
                    );
                }

                $newStock = $validated['type'] === 'IN'
                    ? $product->stock + $validated['quantity']
                    : $product->stock - $validated['quantity'];

                $product->update(['stock' => $newStock]);

                StockMovement::create([
                    'product_id' => $product->id,
                    'type'       => $validated['type'],
                    'quantity'   => $validated['quantity'],
                    'notes'      => $validated['notes'] ?? null,
                ]);
            });

            return redirect()->route('stock.index')
                ->with('success', 'Transaksi stok & histori berhasil dicatat.');
        } catch (Exception $e) {
            return redirect()->route('stock.index')
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}