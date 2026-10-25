@extends('layouts.app')
@section('title', 'Daftar Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Transaksi Penjualan (Sales / Stock-Out)</h4>
    <a href="{{ route('sales.create') }}" class="btn btn-success">+ Buat Penjualan Baru</a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('sales.index') }}" method="GET" class="row g-2">
            <div class="col-md-3"><input type="text" name="search" class="form-control" placeholder="Cari No. Invoice..." value="{{ request('search') }}"></div>
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
                <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-dark">
                <tr><th>No. Invoice</th><th>Tanggal</th><th>Staff</th><th>Item</th><th>Total</th><th>Status</th><th class="text-end">Detail</th></tr>
            </thead>
            <tbody>
                @forelse($sales as $s)
                <tr>
                    <td class="fw-bold">{{ $s->sale_number }}</td>
                    <td>{{ $s->sold_at->format('d M Y H:i') }}</td>
                    <td>{{ $s->user->name }}</td>
                    <td>{{ $s->items_count }}</td>
                    <td class="fw-semibold text-success">Rp {{ number_format($s->total, 0, ',', '.') }}</td>
                    <td><span class="badge {{ $s->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($s->status) }}</span></td>
                    <td class="text-end"><a href="{{ route('sales.show', $s) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada transaksi penjualan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($sales->hasPages())
        <div class="card-footer bg-white">{{ $sales->links() }}</div>
    @endif
</div>
@endsection