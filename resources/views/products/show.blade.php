@extends('layouts.app')
@section('title', 'Detail Produk - Laravel')

@section('content')
<div class="card">
    <h2 style="margin-top:0;">Detail Produk: {{ $product->name }}</h2>
    <p><strong>SKU:</strong> <code>{{ $product->sku }}</code></p>
    <p><strong>Kategori:</strong> <span class="badge">{{ $product->category->name }}</span></p>
    <p><strong>Harga Jual:</strong> Rp {{ number_format($product->price, 0, ',', '.') }}</p>
    <p><strong>Stok Saat Ini:</strong> {{ $product->stock }} Unit (Min: {{ $product->minimum_stock }})</p>
    <p><strong>Supplier Terkait:</strong>
        @forelse($product->suppliers as $sup)
            {{ $sup->name }} (Harga Beli: Rp {{ number_format($sup->pivot->supplier_price, 0, ',', '.') }})
        @empty
            Belum ada supplier
        @endforelse
    </p>
    <a href="{{ route('products.index') }}" class="btn btn-secondary">Kembali ke Daftar</a>
</div>
@endsection