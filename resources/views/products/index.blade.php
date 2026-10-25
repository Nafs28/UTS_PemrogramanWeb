@extends('layouts.app')
@section('title', 'Daftar Produk - Laravel')

@section('content')
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px;">
        <h2 style="margin:0;">Daftar Produk Inventaris</h2>
        <a href="{{ route('products.create') }}" class="btn btn-primary">+ Tambah Produk</a>
    </div>

    <!-- Form Search, Filter & Sorting dengan Method GET -->
    <form method="GET" action="{{ route('products.index') }}" style="display:flex; flex-wrap:wrap; gap:8px; margin-bottom:16px;">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau SKU..." style="flex:1 1 200px; width:auto;">

        <select name="category_id" style="width:auto;">
            <option value="">Semua Kategori</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ (string)request('category_id') === (string)$c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
            @endforeach
        </select>

        <select name="sort" style="width:auto;">
            <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>Urut: Nama</option>
            <option value="sku" {{ $sort === 'sku' ? 'selected' : '' }}>Urut: SKU</option>
            <option value="price" {{ $sort === 'price' ? 'selected' : '' }}>Urut: Harga</option>
            <option value="stock" {{ $sort === 'stock' ? 'selected' : '' }}>Urut: Stok</option>
        </select>

        <select name="dir" style="width:auto;">
            <option value="ASC" {{ $dir === 'ASC' ? 'selected' : '' }}>Naik (ASC)</option>
            <option value="DESC" {{ $dir === 'DESC' ? 'selected' : '' }}>Turun (DESC)</option>
        </select>

        <button type="submit" class="btn btn-primary">Filter</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Reset</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>SKU</th>
                <th>Nama Produk</th>
                <th>Kategori (Eloquent)</th>
                <th>Harga</th>
                <th>Stok</th>
                <th>Aksi (Resource Route)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td><code>{{ $product->sku }}</code></td>
                    <!-- Kurung kurawal ganda Blade otomatis melakukan HTML escaping (Anti-XSS Slide 21) -->
                    <td class="product-name">{{ $product->name }}</td>
                    <td><span class="badge">{{ $product->category->name }}</span></td>
                    <td>Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                    <td>
                        <strong>{{ $product->stock }}</strong>
                        @if($product->stock <= $product->minimum_stock)
                            <span class="badge badge-low">Rendah</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-secondary btn-sm">Detail</a>
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm">Edit</a>
                            <form method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('Hapus produk ini?');" style="margin:0;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#64748b;">Belum ada produk yang ditemukan.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 16px;">
        {{ $products->links() }}
    </div>
</div>
@endsection