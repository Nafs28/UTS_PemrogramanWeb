@extends('layouts.app')
@section('title', 'Transaksi Stok IN/OUT - Laravel')

@section('content')
<div style="display:flex; flex-wrap:wrap; gap:20px;">
    <div class="card" style="flex:1 1 320px; align-self:flex-start;">
        <h3 style="margin-top:0;">🔄 Catat Barang Masuk / Keluar</h3>
        <form method="POST" action="{{ route('stock.store') }}">
            @csrf
            <div class="form-group">
                <label>Pilih Produk</label>
                <select name="product_id" required>
                    <option value="">-- Pilih Produk --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ (string)old('product_id') === (string)$p->id ? 'selected' : '' }}>
                            [{{ $p->sku }}] {{ $p->name }} (Stok: {{ $p->stock }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Jenis Transaksi</label>
                <select name="type" required>
                    <option value="IN" {{ old('type') === 'IN' ? 'selected' : '' }}>Barang Masuk (+ IN)</option>
                    <option value="OUT" {{ old('type') === 'OUT' ? 'selected' : '' }}>Barang Keluar (- OUT)</option>
                </select>
            </div>
            <div class="form-group">
                <label>Jumlah Unit (Quantity &gt; 0)</label>
                <input type="number" name="quantity" min="1" value="{{ old('quantity', 1) }}" required>
            </div>
            <div class="form-group">
                <label>Catatan</label>
                <input name="notes" value="{{ old('notes') }}" placeholder="Contoh: Pengiriman supplier">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;">Simpan Transaksi</button>
        </form>
    </div>

    <div class="card" style="flex:2 1 520px;">
        <h3 style="margin-top:0;">📜 Histori Pergerakan Stok</h3>
        <table>
            <thead><tr><th>Waktu</th><th>Produk</th><th>Tipe</th><th>Qty</th><th>Catatan</th></tr></thead>
            <tbody>
                @foreach($movements as $m)
                <tr>
                    <td style="font-size:12px;color:#64748b;">{{ $m->created_at->format('d M Y H:i') }}</td>
                    <td><code>{{ $m->product->sku }}</code> {{ $m->product->name }}</td>
                    <td>
                        <span class="badge {{ $m->type === 'IN' ? 'badge-in' : 'badge-out' }}">{{ $m->type }}</span>
                    </td>
                    <td><strong>{{ $m->quantity }}</strong></td>
                    <td>{{ $m->notes ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div style="margin-top:12px;">{{ $movements->links() }}</div>
    </div>
</div>
@endsection