@extends('layouts.app')
@section('title', 'Detail Produk')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Detail Produk: {{ $product->name }}</h4>
    <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">← Kembali</a>
</div>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <p><strong>SKU:</strong> <code>{{ $product->sku }}</code></p>
                <p><strong>Kategori:</strong> {{ $product->category->name }}</p>
                <p><strong>Harga Beli:</strong> Rp {{ number_format($product->purchase_price, 0, ',', '.') }}</p>
                <p><strong>Harga Jual:</strong> Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                <p><strong>Stok Saat Ini:</strong> <span class="badge {{ $product->stock <= $product->minimum_stock ? 'bg-danger' : 'bg-success' }}">{{ $product->stock }}</span></p>
                <p class="mb-0"><strong>Minimum Stok:</strong> {{ $product->minimum_stock }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white fw-bold">Riwayat Purchase (Stock-In)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>No. PO</th><th>Supplier</th><th>Qty</th><th>Harga Beli</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($product->purchaseItems as $pi)
                        <tr>
                            <td><a href="{{ route('purchases.show', $pi->purchase_id) }}">{{ $pi->purchase->purchase_number }}</a></td>
                            <td>{{ $pi->purchase->supplier->name }}</td>
                            <td class="text-success fw-bold">+{{ $pi->quantity }}</td>
                            <td>Rp {{ number_format($pi->unit_cost, 0, ',', '.') }}</td>
                            <td>{{ strtoupper($pi->purchase->status) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada riwayat pembelian.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Riwayat Sales (Stock-Out)</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>No. Invoice</th><th>Kasir</th><th>Qty</th><th>Harga Jual</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($product->saleItems as $si)
                        <tr>
                            <td><a href="{{ route('sales.show', $si->sale_id) }}">{{ $si->sale->sale_number }}</a></td>
                            <td>{{ $si->sale->user->name }}</td>
                            <td class="text-danger fw-bold">-{{ $si->quantity }}</td>
                            <td>Rp {{ number_format($si->unit_price, 0, ',', '.') }}</td>
                            <td>{{ strtoupper($si->sale->status) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada riwayat penjualan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection