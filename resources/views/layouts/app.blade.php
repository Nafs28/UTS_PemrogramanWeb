<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inventory Laravel - Pertemuan 5')</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        body { margin: 0; padding: 20px 16px; font-family: Arial, sans-serif; background: #f1f5f9; color: #0f172a; line-height: 1.5; }
        .container { max-width: 1100px; margin: 0 auto; }
        .card { background: #fff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 20px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(15,23,42,0.04); }
        .navbar { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 12px; }
        .nav-links { display: flex; flex-wrap: wrap; gap: 10px; }
        .nav-links a { text-decoration: none; padding: 8px 14px; border-radius: 8px; font-weight: 600; font-size: 14px; color: #334155; background: #f8fafc; }
        .nav-links a:hover, .nav-links a.active { background: #2563eb; color: #fff; }
        .widgets { display: flex; flex-wrap: wrap; gap: 14px; margin-bottom: 20px; }
        .widget { flex: 1 1 190px; background: #fff; border: 1px solid #cbd5e1; border-radius: 12px; padding: 16px; }
        .widget-title { font-size: 12px; color: #64748b; text-transform: uppercase; font-weight: 700; }
        .widget-value { font-size: 22px; font-weight: 700; margin-top: 6px; }
        table { width: 100%; border-collapse: collapse; font-size: 14px; }
        th, td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; text-align: left; }
        th { background: #f8fafc; }
        .product-name { font-weight: 400; } /* Agar uji Anti-XSS <b>Promo</b> terlihat jelas */
        .badge { display: inline-block; padding: 3px 9px; font-size: 12px; border-radius: 99px; font-weight: 600; background: #e2e8f0; color: #334155; }
        .badge-in { background: #dcfce7; color: #166534; }
        .badge-out { background: #fee2e2; color: #991b1b; }
        .badge-low { background: #fef3c7; color: #92400e; }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 16px; font-weight: 600; font-size: 14px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .form-group { margin-bottom: 14px; }
        .form-group label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 14px; }
        input, select { padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; width: 100%; }
        .error-msg { color: #dc2626; font-size: 12px; margin-top: 4px; display: block; }
        .btn, button { padding: 9px 14px; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-primary { background: #2563eb; color: #fff; }
        .btn-warning { background: #f59e0b; color: #fff; }
        .btn-danger { background: #dc2626; color: #fff; }
        .btn-secondary { background: #64748b; color: #fff; }
        .btn-sm { padding: 5px 10px; font-size: 12px; }
        nav[role="navigation"] svg { width: 18px; height: 18px; vertical-align: middle; }
    </style>
</head>
<body>
<div class="container">
    <header class="card navbar">
        <div>
            <strong style="font-size: 18px;">📦 Inventory Laravel (MVC)</strong>
            <span class="badge" style="background:#dbeafe;color:#1e40af;margin-left:6px;">Pertemuan 5</span>
        </div>
        <nav class="nav-links">
            <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">📊 Dashboard</a>
            <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">📋 Produk (CRUD)</a>
            <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">🏷️ Kategori</a>
            <a href="{{ route('stock.index') }}" class="{{ request()->routeIs('stock.*') ? 'active' : '' }}">🔄 Transaksi Stok (IN/OUT)</a>
            <a href="{{ route('test.runner') }}" class="{{ request()->routeIs('test.runner') ? 'active' : '' }}">✅ Test Runner</a>
        </nav>
    </header>

    @if(session('success'))
        <x-alert type="success">{{ session('success') }}</x-alert>
    @endif

    @if(session('error'))
        <x-alert type="error">{{ session('error') }}</x-alert>
    @endif

    <main>
        @yield('content')
    </main>
</div>
</body>
</html>