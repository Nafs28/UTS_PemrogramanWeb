@extends('layouts.app')
@section('title', 'Produk Stok Rendah')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="fw-bold text-danger mb-0">⚠️ Daftar Produk Stok Rendah (Low-Stock List)</h4>
        <small class="text-muted">Menampilkan produk yang stoknya berada di bawah atau sama dengan batas minimum</small>
    </div>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ Restock via Purchase (Stock-In)</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr><th>SKU</th><th>Nama Produk</th><th>Kategori</th><th>Stok Tersedia</th><th>Minimum Stok</th><th>Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td><code>{{ $product->sku }}</code></td>
                    <td class="fw-semibold">{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td><span class="badge bg-danger fs-6">{{ $product->stock }}</span></td>
                    <td>{{ $product->minimum_stock }}</td>
                    <td><a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-4 text-muted">Seluruh produk memiliki stok di atas batas minimum.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white">{{ $products->links() }}</div>
    @endif
</div>
@endsection