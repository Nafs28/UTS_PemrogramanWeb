<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'user'])->withCount('items');

        if ($request->filled('search')) {
            $query->where('purchase_number', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('purchased_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('purchased_at', '<=', $request->end_date);
        }

        $purchases = $query->latest('purchased_at')->paginate(10)->withQueryString();
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $suppliers = Supplier::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request) {
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'purchase_number' => 'PO-' . now()->format('YmdHis') . '-' . rand(100, 999),
                'total' => 0,
                'status' => 'completed',
                'purchased_at' => now(),
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                $qty = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];

                if ($qty < 1 || $unitCost < 0) {
                    throw ValidationException::withMessages([
                        'items' => 'Kuantitas harus > 0 dan harga beli tidak boleh negatif.',
                    ]);
                }

                $subtotal = $unitCost * $qty;

                $purchase->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                $product->increment('stock', $qty);
                $product->update(['purchase_price' => $unitCost]);
                $total += $subtotal;
            }

            $purchase->update(['total' => $total]);
        });

        return redirect()->route('purchases.index')->with('success', 'Pembelian (Stock-In) berhasil disimpan dan stok produk telah bertambah.');
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'user', 'items.product']);
        return view('purchases.show', compact('purchase'));
    }

    public function cancel(Purchase $purchase)
    {
        Gate::authorize('admin-only');
        if ($purchase->status === 'cancelled') {
            return back()->with('error', 'Transaksi sudah dibatalkan sebelumnya.');
        }

        try {
            DB::transaction(function () use ($purchase) {
                $purchase->load('items');
                foreach ($purchase->items as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item->product_id);
                    if ($product->stock < $item->quantity) {
                        throw ValidationException::withMessages([
                            'cancel' => "Pembatalan ditolak! Stok {$product->name} saat ini ({$product->stock}) kurang dari jumlah pembelian yang akan ditarik ({$item->quantity}).",
                        ]);
                    }
                    $product->decrement('stock', $item->quantity);
                }
                $purchase->update(['status' => 'cancelled']);
            });
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('purchases.show', $purchase)->with('success', 'Transaksi pembelian dibatalkan dan stok produk telah disesuaikan kembali.');
    }
}