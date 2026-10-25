@extends('layouts.app')
@section('title', 'Test Runner Pertemuan 5 - Laravel')

@section('content')
<div class="card">
    <h2 style="margin-top:0;">✅ Automated Test Runner — Checklist Pertemuan 5 (Slide 46)</h2>
    <table>
        <thead>
            <tr><th>No</th><th>Status</th><th>Kategori</th><th>Checklist Pengujian</th><th>Detail</th></tr>
        </thead>
        <tbody>
            @foreach($checks as $i => $c)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>
                    <span class="badge {{ $c['pass'] ? 'badge-in' : 'badge-out' }}">
                        {{ $c['pass'] ? 'PASS ✓' : 'FAIL ✗' }}
                    </span>
                </td>
                <td><span class="badge">{{ $c['category'] }}</span></td>
                <td><strong>{{ $c['title'] }}</strong></td>
                <td style="color:#475569;">{{ $c['detail'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection