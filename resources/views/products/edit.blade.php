@extends('layouts.app')
@section('title', 'Edit Produk - Laravel')

@section('content')
<div class="card" style="max-width: 580px; margin: 0 auto;">
    <h2 style="margin-top:0;">Edit Produk: {{ $product->sku }}</h2>

    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Kode SKU</label>
            <input name="sku" value="{{ old('sku', $product->sku) }}" required>
            @error('sku') <small class="error-msg">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label>Nama Produk</label>
            <input name="name" value="{{ old('name', $product->name) }}" required>
            @error('name') <small class="error-msg">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label>Kategori</label>
            <select name="category_id" required>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ (int)old('category_id', $product->category_id) === $c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <small class="error-msg">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label>Harga Jual (Rp)</label>
            <input type="number" step="any" name="price" value="{{ old('price', $product->price) }}" required>
            @error('price') <small class="error-msg">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label>Batas Minimum Stok</label>
            <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $product->minimum_stock) }}" required>
            @error('minimum_stock') <small class="error-msg">{{ $message }}</small> @enderror
        </div>

        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection