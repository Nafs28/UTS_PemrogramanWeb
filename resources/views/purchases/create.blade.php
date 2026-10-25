@extends('layouts.app')
@section('title', 'Buat Pembelian')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Buat Transaksi Pembelian (Multi-Item Stock-In)</h4>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">← Kembali</a>
</div>

<form action="{{ route('purchases.store') }}" method="POST">
    @csrf
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <label class="form-label fw-semibold">Pilih Supplier</label>
            <select name="supplier_id" class="form-select" required>
                <option value="">-- Pilih Supplier --</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->phone }})</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span>Item Pembelian</span>
            <button type="button" id="addPoRow" class="btn btn-sm btn-warning fw-semibold">+ Tambah Item</button>
        </div>
        <div class="card-body">
            <table class="table align-middle" id="poTable">
                <thead><tr><th>Produk</th><th>Quantity</th><th>Harga Beli Satuan (Rp)</th><th></th></tr></thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="items[0][product_id]" class="form-select" required>
                                <option value="">-- Pilih Produk --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">[{{ $p->sku }}] {{ $p->name }} (Stok: {{ $p->stock }})</option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="items[0][quantity]" class="form-control" value="1" min="1" required></td>
                        <td><input type="number" name="items[0][unit_cost]" class="form-control" value="50000" min="0" required></td>
                        <td><button type="button" class="btn btn-outline-danger btn-sm rm-row">✕</button></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white text-end">
            <button type="submit" class="btn btn-primary px-4">Simpan Pembelian & Tambah Stok</button>
        </div>
    </div>
</form>

@push('scripts')
<script>
let idx = 1;
document.getElementById('addPoRow').addEventListener('click', () => {
    const tbody = document.querySelector('#poTable tbody');
    const tr = tbody.querySelector('tr').cloneNode(true);
    tr.querySelectorAll('select, input').forEach(el => {
        el.name = el.name.replace(/\[\d+\]/, `[${idx}]`);
    });
    tbody.appendChild(tr);
    idx++;
});
document.addEventListener('click', e => {
    if (e.target.classList.contains('rm-row')) {
        const tbody = document.querySelector('#poTable tbody');
        if (tbody.querySelectorAll('tr').length > 1) e.target.closest('tr').remove();
    }
});
</script>
@endpush
@endsection