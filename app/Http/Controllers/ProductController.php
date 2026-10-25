<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // GET /products (index) - Search, Filter, Allowlist Sorting & Pagination (Slide 32 & 38)
    public function index(Request $request)
    {
        $allowedSorts = ['name', 'sku', 'price', 'stock'];
        $sort = in_array($request->sort, $allowedSorts, true) ? $request->sort : 'name';
        $dir = strtoupper($request->dir ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

        $products = Product::with(['category', 'suppliers'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%");
                });
            })
            ->when($request->category_id, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->orderBy($sort, $dir)
            ->paginate(6)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        return view('products.index', compact('products', 'categories', 'sort', 'dir'));
    }

    // GET /products/create (create)
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('products.create', compact('categories', 'suppliers'));
    }

    // POST /products (store) - Validasi & Mass Assignment (Slide 19 & 31)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id'   => ['required', 'exists:categories,id'],
            'sku'           => ['required', 'string', 'min:3', 'max:50', 'unique:products,sku'],
            'name'          => ['required', 'string', 'min:3', 'max:150'],
            'price'         => ['required', 'numeric', 'gt:0'],
            'stock'         => ['required', 'integer', 'min:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'supplier_id'   => ['nullable', 'exists:suppliers,id'],
        ]);

        $validated['sku'] = strtoupper($validated['sku']);
        $product = Product::create($validated);

        if ($request->filled('supplier_id')) {
            $product->suppliers()->sync([
                $request->supplier_id => ['supplier_price' => $validated['price'] * 0.8]
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Produk baru berhasil disimpan.');
    }

    // GET /products/{product} (show) - Implicit Route Model Binding (Slide 14)
    public function show(Product $product)
    {
        $product->load(['category', 'suppliers', 'stockMovements']);
        return view('products.show', compact('product'));
    }

    // GET /products/{product}/edit (edit)
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    // PUT /products/{product} (update)
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id'   => ['required', 'exists:categories,id'],
            'sku'           => ['required', 'string', 'min:3', 'max:50', 'unique:products,sku,' . $product->id],
            'name'          => ['required', 'string', 'min:3', 'max:150'],
            'price'         => ['required', 'numeric', 'gt:0'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'supplier_id'   => ['nullable', 'exists:suppliers,id'],
        ]);

        $validated['sku'] = strtoupper($validated['sku']);
        $product->update($validated);

        if ($request->filled('supplier_id')) {
            $product->suppliers()->sync([
                $request->supplier_id => ['supplier_price' => $validated['price'] * 0.8]
            ]);
        }

        return redirect()->route('products.index')
            ->with('success', 'Data produk berhasil diperbarui.');
    }

    // DELETE /products/{product} (destroy) - Menerapkan Business Rule P4 ON DELETE RESTRICT
    public function destroy(Product $product)
    {
        try {
            $product->delete();
            return redirect()->route('products.index')
                ->with('success', 'Produk berhasil dihapus.');
        } catch (QueryException $e) {
            return redirect()->route('products.index')
                ->with('error', 'DITOLAK (Restrict On Delete): Produk ini sudah memiliki riwayat transaksi stok dan tidak boleh dihapus!');
        }
    }
}