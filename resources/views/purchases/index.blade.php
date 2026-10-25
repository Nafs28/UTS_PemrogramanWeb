@extends('layouts.app')
@section('title', 'Daftar Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Transaksi Pembelian (Purchase / Stock-In)</h4>
    <a href="{{ route('purchases.create') }}" class="btn btn-primary">+ Buat Purchase Baru</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('purchases.index') }}" method="GET" class="row g-2">
            <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Cari No. PO..." value="{{ request('search') }}"></div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">-- Status --</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2"><input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}"></div>
            <div class="col-md-2"><input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}"></div>
            <div class="col-md-3 d-flex gap-1">
                <button type="submit" class="btn btn-dark w-100">Filter</button>
                <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr><th>No. PO</th><th>Tanggal</th><th>Supplier</th><th>Staff</th><th>Item</th><th>Total</th><th>Status</th><th class="text-end">Detail</th></tr>
            </thead>
            <tbody>
                @forelse($purchases as $po)
                <tr>
                    <td class="fw-bold">{{ $po->purchase_number }}</td>
                    <td>{{ $po->purchased_at->format('d M Y H:i') }}</td>
                    <td>{{ $po->supplier->name }}</td>
                    <td>{{ $po->user->name }}</td>
                    <td>{{ $po->items_count }}</td>
                    <td class="fw-semibold">Rp {{ number_format($po->total, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $po->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($po->status) }}</span></td>
                    <td class="text-end"><a href="{{ route('purchases.show', $po) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada transaksi pembelian.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($purchases->hasPages())
        <div class="card-footer bg-white">{{ $purchases->links() }}</div>
    @endif
</div>
@endsection