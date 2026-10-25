@extends('layouts.app')
@section('title', 'Tambah Produk - Laravel')

@section('content')
<div class="card" style="max-width: 580px; margin: 0 auto;">
    <h2 style="margin-top:0;">Tambah Produk Baru</h2>

    <form method="POST" action="{{ route('products.store') }}">
        @csrf

        <div class="form-group">
            <label>Kode SKU (Unik)</label>
            <input name="sku" value="{{ old('sku') }}" placeholder="Contoh: KOP-009" required>
            @error('sku') <small class="error-msg">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label>Nama Produk</label>
            <input name="name" value="{{ old('name') }}" placeholder="Contoh: Kopi Arabika Flores" required>
            @error('name') <small class="error-msg">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label>Kategori</label>
            <select name="category_id" required>
                <option value="">-- Pilih Kategori --</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" {{ (string)old('category_id') === (string)$c->id ? 'selected' : '' }}>
                        {{ $c->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id') <small class="error-msg">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label>Supplier Utama (Opsional - Relasi Many-to-Many)</label>
            <select name="supplier_id">
                <option value="">-- Tanpa Supplier --</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ (string)old('supplier_id') === (string)$s->id ? 'selected' : '' }}>
                        {{ $s->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Harga Jual (Rp)</label>
            <input type="number" step="any" name="price" value="{{ old('price') }}" required>
            @error('price') <small class="error-msg">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label>Stok Awal</label>
            <input type="number" name="stock" value="{{ old('stock', 0) }}" required>
            @error('stock') <small class="error-msg">{{ $message }}</small> @enderror
        </div>

        <div class="form-group">
            <label>Batas Minimum Stok</label>
            <input type="number" name="minimum_stock" value="{{ old('minimum_stock', 5) }}" required>
            @error('minimum_stock') <small class="error-msg">{{ $message }}</small> @enderror
        </div>

        <div style="display:flex; gap:8px;">
            <button type="submit" class="btn btn-primary">Simpan Produk</button>
            <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
        </div>
    </form>
</div>
@endsection