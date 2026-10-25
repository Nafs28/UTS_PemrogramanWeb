@extends('layouts.app')
@section('title', 'Login')

@section('content')
<div class="row justify-content-center align-items-center" style="min-height: 72vh;">
    <div class="col-md-5 col-lg-4">
        <div class="card border-0">
            <div class="card-header text-center py-4 border-bottom">
                <h5 class="fw-bold mb-1 text-white">Inventory & Sales Management</h5>
                <small class="text-muted">UTS Web Programming — Toko Kopi Nusantara</small>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('login.submit') }}" method="POST" autocomplete="off">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-uppercase text-muted">Email</label>
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            value="{{ old('email') }}"
                            placeholder="Masukkan alamat email"
                            required
                            autofocus
                        >
                    </div>
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-uppercase text-muted">Password</label>
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Masukkan password"
                            required
                        >
                    </div>
                    <button type="submit" class="btn btn-dark w-100 py-2">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection