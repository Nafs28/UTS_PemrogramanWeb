@extends('layouts.app')
@section('title', 'Manajemen Pengguna & Role')

@section('content')
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-dark text-white fw-semibold">Tambah Pengguna Baru (Admin Only)</div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role Akses</label>
                        <select name="role" class="form-select" required>
                            <option value="staff">Staff (Kasir / Gudang)</option>
                            <option value="admin">Admin / Owner</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-dark w-100">Simpan Pengguna</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Daftar Pengguna & Pengaturan Role</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Nama & Email</th><th>Transaksi</th><th>Role Saat Ini</th><th>Ubah Role</th></tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $u->name }}</div>
                                <small class="text-muted">{{ $u->email }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $u->sales_count }} Sales</span>
                                <span class="badge bg-light text-dark border">{{ $u->purchases_count }} PO</span>
                            </td>
                            <td>
                                <span class="badge {{ $u->role === 'admin' ? 'bg-warning text-dark' : 'bg-info text-dark' }}">
                                    {{ strtoupper($u->role) }}
                                </span>
                            </td>
                            <td>
                                @if($u->id !== auth()->id())
                                <form action="{{ route('users.update_role', $u) }}" method="POST" class="d-flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="role" class="form-select form-select-sm" style="width:110px;">
                                        <option value="staff" {{ $u->role === 'staff' ? 'selected' : '' }}>STAFF</option>
                                        <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>ADMIN</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-dark">Update</button>
                                </form>
                                @else
                                <small class="text-muted fst-italic">Akun Anda</small>
                                @endif
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