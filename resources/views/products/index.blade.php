@extends('layouts.app')
@section('title', 'Daftar Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Master Data Produk</h4>
    @can('admin-only')
        <a href="{{ route('products.create') }}" class="btn btn-dark">+ Tambah Produk</a>
    @endcan
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('products.index') }}" method="GET" class="row g-2">
            <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Cari SKU atau nama produk..." value="{{ request('search') }}"></div>
            <div class="col-md-3">
                <select name="category_id" class="form-select">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="stock_status" class="form-select">
                    <option value="">-- Semua Status Stok --</option>
                    <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Stok Rendah (&le; Min)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr><th>SKU</th><th>Nama Produk</th><th>Kategori</th><th>Harga Beli</th><th>Harga Jual</th><th>Stok</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td><code>{{ $product->sku }}</code></td>
                    <td class="fw-semibold">{{ $product->name }}</td>
                    <td>{{ $product->category->name }}</td>
                    <td>Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                    <td class="fw-semibold text-success">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</td>
                    <td>
                        <span class="badge {{ $product->stock <= $product->minimum_stock ? 'bg-danger' : 'bg-success' }}">
                            {{ $product->stock }} (Min: {{ $product->minimum_stock }})
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">Detail</a>
                        @can('admin-only')
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-sm btn-outline-warning">Edit</a>
                            <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus produk ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                            </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Tidak ada data produk.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
        <div class="card-footer bg-white">{{ $products->links() }}</div>
    @endif
</div>
@endsection