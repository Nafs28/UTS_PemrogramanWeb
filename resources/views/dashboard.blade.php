@extends('layouts.app')
@section('title', 'Dashboard Inventory - Laravel')

@section('content')
<div class="widgets">
    <div class="widget">
        <div class="widget-title">Total Produk</div>
        <div class="widget-value">{{ $totalProducts }} Item</div>
    </div>
    <div class="widget">
        <div class="widget-title">Nilai Inventory</div>
        <div class="widget-value" style="color:#0f766e;">Rp {{ number_format($inventoryValue, 0, ',', '.') }}</div>
    </div>
    <div class="widget">
        <div class="widget-title">Produk Stok Rendah</div>
        <div class="widget-value" style="color:#d97706;">{{ $lowStockProducts->count() }} Item</div>
    </div>
    <div class="widget">
        <div class="widget-title">Total Masuk (IN)</div>
        <div class="widget-value" style="color:#16a34a;">+{{ $totalIn }}</div>
    </div>
    <div class="widget">
        <div class="widget-title">Total Keluar (OUT)</div>
        <div class="widget-value" style="color:#dc2626;">-{{ $totalOut }}</div>
    </div>
</div>

@if($lowStockProducts->count())
<div class="card" style="border-left: 5px solid #f59e0b;">
    <h3 style="margin-top:0;color:#b45309;">⚠️ Peringatan Produk Stok Rendah (Stock &le; Minimum Stock)</h3>
    <table>
        <thead>
            <tr><th>SKU</th><th>Nama Produk</th><th>Kategori</th><th>Stok</th><th>Min. Stok</th></tr>
        </thead>
        <tbody>
            @foreach($lowStockProducts as $lp)
            <tr>
                <td><code>{{ $lp->sku }}</code></td>
                <td class="product-name">{{ $lp->name }}</td>
                <td><span class="badge">{{ $lp->category->name }}</span></td>
                <td><span class="badge badge-low">{{ $lp->stock }}</span></td>
                <td>{{ $lp->minimum_stock }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="card">
    <h3 style="margin-top:0;">📊 Laporan Agregat Eloquent per Kategori (withCount, withSum, withAvg)</h3>
    <table>
        <thead>
            <tr><th>Kategori</th><th>Jumlah Produk (COUNT)</th><th>Total Unit Stok (SUM)</th><th>Rata-rata Harga (AVG)</th></tr>
        </thead>
        <tbody>
            @foreach($categoryReport as $cat)
            <tr>
                <td><strong>{{ $cat->name }}</strong></td>
                <td>{{ $cat->products_count }} Produk</td>
                <td>{{ $cat->products_sum_stock ?? 0 }} Unit</td>
                <td>Rp {{ number_format($cat->products_avg_price ?? 0, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection