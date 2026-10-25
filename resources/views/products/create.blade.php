@extends('layouts.app')
@section('title', 'Tambah Produk')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">Tambah Produk Baru</div>
            <div class="card-body p-4">
                <form action="{{ route('products.store') }}" method="POST">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-4"><label class="form-label">SKU (Unik)</label><input type="text" name="sku" class="form-control" value="{{ old('sku') }}" required></div>
                        <div class="col-md-8"><label class="form-label">Nama Produk</label><input type="text" name="name" class="form-control" value="{{ old('name') }}" required></div>
                        <div class="col-md-12">
                            <label class="form-label">Kategori</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($categories as $c)
                                    <option value="{{ $c->id }}" {{ old('category_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6"><label class="form-label">Harga Beli (Rp)</label><input type="number" name="purchase_price" class="form-control" value="{{ old('purchase_price', 0) }}" min="0" required></div>
                        <div class="col-md-6"><label class="form-label">Harga Jual (Rp)</label><input type="number" name="selling_price" class="form-control" value="{{ old('selling_price', 0) }}" min="0" required></div>
                        <div class="col-md-6"><label class="form-label">Stok Awal</label><input type="number" name="stock" class="form-control" value="{{ old('stock', 0) }}" min="0" required></div>
                        <div class="col-md-6"><label class="form-label">Minimum Stok</label><input type="number" name="minimum_stock" class="form-control" value="{{ old('minimum_stock', 5) }}" min="0" required></div>
                    </div>
                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-dark">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection