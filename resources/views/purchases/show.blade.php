@extends('layouts.app')
@section('title', 'Detail PO')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Detail Purchase: {{ $purchase->purchase_number }}</h4>
    <div class="d-flex gap-2">
        @can('admin-only')
            @if($purchase->status === 'completed')
            <form action="{{ route('purchases.cancel', $purchase) }}" method="POST" onsubmit="return confirm('Batalkan transaksi pembelian ini?')">
                @csrf @method('PATCH')
                <button class="btn btn-danger">Batalkan Transaksi</button>
            </form>
            @endif
        @endcan
        <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">← Kembali</a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body row">
        <div class="col-md-4"><strong>Supplier:</strong> {{ $purchase->supplier->name }}</div>
        <div class="col-md-3"><strong>Petugas:</strong> {{ $purchase->user->name }}</div>
        <div class="col-md-3"><strong>Waktu:</strong> {{ $purchase->purchased_at->format('d M Y H:i') }}</div>
        <div class="col-md-2"><strong>Status:</strong> <span class="badge {{ $purchase->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($purchase->status) }}</span></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <table class="table align-middle mb-0">
        <thead class="table-light"><tr><th>SKU</th><th>Produk</th><th>Qty</th><th>Unit Cost</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
            @foreach($purchase->items as $item)
            <tr>
                <td><code>{{ $item->product->sku }}</code></td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp {{ number_format($item->unit_cost, 0, ',', '.') }}</td>
                <td class="text-end fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="table-light">
            <tr><th colspan="4" class="text-end">Total:</th><th class="text-end fs-5">Rp {{ number_format($purchase->total, 0, ',', '.') }}</th></tr>
        </tfoot>
    </table>
</div>
@endsection