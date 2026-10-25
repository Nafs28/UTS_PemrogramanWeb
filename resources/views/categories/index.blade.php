@extends('layouts.app')
@section('title', 'Kategori Produk - Laravel')

@section('content')
<div style="display:flex; flex-wrap:wrap; gap:20px;">
    <div class="card" style="flex:1 1 300px; align-self:flex-start;">
        <h3 style="margin-top:0;">+ Tambah Kategori</h3>
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            <div class="form-group">
                <label>Nama Kategori</label>
                <input name="name" value="{{ old('name') }}" required placeholder="Contoh: Sirup Rasa">
                @error('name') <small class="error-msg">{{ $message }}</small> @enderror
            </div>
            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>

    <div class="card" style="flex:2 1 480px;">
        <h3 style="margin-top:0;">Daftar Kategori (Relasi Category hasMany Product)</h3>
        <table>
            <thead><tr><th>ID</th><th>Nama Kategori</th><th>Jumlah Produk</th><th>Aksi</th></tr></thead>
            <tbody>
                @foreach($categories as $c)
                <tr>
                    <td>{{ $c->id }}</td>
                    <td><strong>{{ $c->name }}</strong></td>
                    <td>{{ $c->products_count }} Produk</td>
                    <td>
                        <form method="POST" action="{{ route('categories.destroy', $c) }}" style="margin:0;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection