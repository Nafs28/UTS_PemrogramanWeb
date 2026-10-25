@extends('layouts.app')
@section('title', 'Kategori Produk')

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">Tambah Kategori</div>
            <div class="card-body">
                <form action="{{ route('categories.store') }}" method="POST">
                    @csrf
                    <div class="mb-3"><label class="form-label">Nama Kategori</label><input type="text" name="name" class="form-control" required></div>
                    <button type="submit" class="btn btn-dark w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Kategori</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Nama Kategori</th><th>Produk</th><th class="text-end">Hapus</th></tr></thead>
                    <tbody>
                        @foreach($categories as $c)
                        <tr>
                            <td>
                                <form action="{{ route('categories.update', $c) }}" method="POST" class="d-flex gap-2">
                                    @csrf @method('PUT')
                                    <input type="text" name="name" class="form-control form-control-sm" value="{{ $c->name }}" required>
                                    <button class="btn btn-sm btn-outline-success">Update</button>
                                </form>
                            </td>
                            <td><span class="badge bg-secondary">{{ $c->products_count }}</span></td>
                            <td class="text-end">
                                <form action="{{ route('categories.destroy', $c) }}" method="POST" onsubmit="return confirm('Hapus kategori?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection