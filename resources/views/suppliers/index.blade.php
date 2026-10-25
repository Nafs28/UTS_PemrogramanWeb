@extends('layouts.app')
@section('title', 'Data Supplier')

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white">Tambah Supplier</div>
            <div class="card-body">
                <form action="{{ route('suppliers.store') }}" method="POST">
                    @csrf
                    <div class="mb-3"><label class="form-label">Nama Supplier</label><input type="text" name="name" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Telepon</label><input type="text" name="phone" class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control"></div>
                    <button type="submit" class="btn btn-dark w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Supplier</div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Data Supplier</th><th>PO</th><th class="text-end">Hapus</th></tr></thead>
                    <tbody>
                        @foreach($suppliers as $s)
                        <tr>
                            <td>
                                <form action="{{ route('suppliers.update', $s) }}" method="POST" class="row g-1">
                                    @csrf @method('PUT')
                                    <div class="col-md-4"><input type="text" name="name" class="form-control form-control-sm" value="{{ $s->name }}" required></div>
                                    <div class="col-md-3"><input type="text" name="phone" class="form-control form-control-sm" value="{{ $s->phone }}" required></div>
                                    <div class="col-md-3"><input type="email" name="email" class="form-control form-control-sm" value="{{ $s->email }}"></div>
                                    <div class="col-md-2"><button class="btn btn-sm btn-outline-success w-100">Update</button></div>
                                </form>
                            </td>
                            <td><span class="badge bg-info text-dark">{{ $s->purchases_count }}</span></td>
                            <td class="text-end">
                                <form action="{{ route('suppliers.destroy', $s) }}" method="POST" onsubmit="return confirm('Hapus supplier?')">
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