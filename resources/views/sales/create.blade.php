@extends('layouts.app')
@section('title', 'Buat Penjualan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Buat Transaksi Penjualan (Multi-Item Stock-Out)</h4>
    <a href="{{ route('sales.index') }}" class="btn btn-outline-secondary">← Kembali</a>
</div>

<form action="{{ route('sales.store') }}" method="POST">
    @csrf
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>Daftar Item Penjualan</span>
            <button type="button" id="addSaleRow" class="btn btn-sm btn-warning fw-semibold">+ Tambah Item</button>
        </div>
        <div class="card-body">
            <table class="table align-middle" id="saleTable">
                <thead><tr><th>Produk (Harga Jual & Stok Tersedia)</th><th>Quantity</th><th></th></tr></thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="items[0][product_id]" class="form-select" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">[{{ $p->sku }}] {{ $p->name }} — Rp {{ number_format($p->selling_price, 0, ',', '.') }} (Stok: {{ $p->stock }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" required></td>
                        <td><button type="button" class="btn btn-outline-danger btn-sm rm-sale-row">✕</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white text-end">
            <button type="submit" class="btn btn-success px-4">Simpan Penjualan & Kurangi Stok</button>
        </div>
    </div>
</form>

@push('scripts')
<script>
let sIdx = 1;
document.getElementById('addSaleRow').addEventListener('click', () => {
    const tbody = document.querySelector('#saleTable tbody');
    const tr = tbody.querySelector('tr').cloneNode(true);
    tr.querySelectorAll('select, input').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${sIdx}]`);
    });
    tbody.appendChild(tr);
    sIdx++;
});
document.addEventListener('click', e => {
    if (e.target.classList.contains('rm-sale-row')) {
        const tbody = document.querySelector('#saleTable tbody');
        if (tbody.querySelectorAll('tr').length > 1) e.target.closest('tr').remove();
    }
});
</script>
@endpush
@endsection