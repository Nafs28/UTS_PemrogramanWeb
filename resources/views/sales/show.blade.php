@extends('layouts.app')
@section('title', 'Detail Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Detail Invoice: {{ $sale->sale_number }}</h4>
    <div class="d-flex gap-2">
        @can('admin-only')
            @if($sale->status === 'completed')
            <form action="{{ route('sales.cancel', $sale) }}" method="POST" onsubmit="return confirm('Batalkan penjualan ini dan kembalikan stok?')">
                @csrf @method('PATCH')
                <button class="btn btn-danger">Batalkan Penjualan (Pulihkan Stok)</button>
            </form>
            @endif
        @endcan
        <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">← Kembali</a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body row">
        <div class="col-md-4"><strong>Staff / Kasir:</strong> {{ $sale->user->name }}</div>
        <div class="col-md-4"><strong>Waktu:</strong> {{ $sale->sold_at->format('d M Y H:i') }}</div>
        <div class="col-md-4"><strong>Status:</strong> <span class="badge {{ $sale->status === 'completed' ? 'bg-success' : 'bg-secondary' }}">{{ strtoupper($sale->status) }}</span></div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <table class="table align-middle mb-0">
        <thead class="table-light"><tr><th>SKU</th><th>Produk</th><th>Qty</th><th>Harga Jual Satuan</th><th class="text-end">Subtotal</th></tr></thead>
        <tbody>
            @foreach($sale->items as $item)
            <tr>
                <td><code>{{ $item->product->sku }}</code></td>
                <td>{{ $item->product->name }}</td>
                <td>{{ $item->quantity }}</td>
                <td>Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                <td class="text-end fw-semibold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot class="table-light">
            <tr><th colspan="4" class="text-end">Total:</th><th class="text-end fs-5 text-success">Rp {{ number_format($sale->total, 0, ',', '.') }}</th></tr>
        </tfoot>
    </table>
</div>
@endsection