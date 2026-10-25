@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Dashboard Inventory & Sales</h3>
        <p class="text-muted mb-0">Ringkasan agregat real-time dari database Toko Kopi Nusantara</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('purchases.create') }}" class="btn btn-outline-primary">+ Purchase (Stock-In)</a>
        <a href="{{ route('sales.create') }}" class="btn btn-success">+ Sales (Stock-Out)</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Total Produk</h6>
                <h3 class="fw-bold">{{ $totalProducts }}</h3>
                <small class="text-muted">{{ $totalCategories }} Kategori • {{ $totalSuppliers }} Supplier</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Penjualan Hari Ini</h6>
                <h3 class="fw-bold text-success">Rp {{ number_format($salesToday, 0, ',', '.') }}</h3>
                <small class="text-muted">Bulan ini: Rp {{ number_format($salesThisMonth, 0, ',', '.') }}</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <h6 class="text-muted">Jumlah Transaksi</h6>
                <h3 class="fw-bold text-primary">{{ $totalSalesCount + $totalPurchasesCount }}</h3>
                <small class="text-muted">{{ $totalSalesCount }} Sales • {{ $totalPurchasesCount }} Purchases</small>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100 border-start border-danger border-4">
            <div class="card-body">
                <h6 class="text-danger">Produk Stok Rendah</h6>
                <h3 class="fw-bold text-danger">{{ $lowStockProducts->count() }}</h3>
                <a href="{{ route('products.low_stock') }}" class="small text-danger">Lihat Daftar Restock →</a>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold text-danger">⚠️ Peringatan Stok Rendah (Stock &le; Minimum)</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>SKU</th><th>Produk</th><th>Stok</th><th>Min</th></tr></thead>
                    <tbody>
                        @forelse($lowStockProducts as $p)
                        <tr>
                            <td><code>{{ $p->sku }}</code></td>
                            <td><a href="{{ route('products.show', $p) }}" class="text-decoration-none fw-semibold">{{ $p->name }}</a></td>
                            <td><span class="badge bg-danger">{{ $p->stock }}</span></td>
                            <td>{{ $p->minimum_stock }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3 text-muted">Semua stok aman.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">🛒 Penjualan Terakhir</div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light"><tr><th>No. Nota</th><th>Staff</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($recentSales as $s)
                        <tr>
                            <td><a href="{{ route('sales.show', $s) }}" class="fw-semibold text-decoration-none">{{ $s->sale_number }}</a></td>
                            <td>{{ $s->user->name }}</td>
                            <td>Rp {{ number_format($s->total, 0, ',', '.') }}</td>
                            <td><span class="badge {{ $s->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($s->status) }}</span></td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection