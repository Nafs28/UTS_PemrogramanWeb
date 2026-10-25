<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Inventory & Sales') — Kopi Nusantara</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-main: #0b0f17;
            --bg-surface: #131a26;
            --bg-elevated: #1a2333;
            --border-subtle: #263147;
            --text-primary: #f1f5f9;
            --text-muted: #94a3b8;
            --accent-gold: #c89d66;
            --accent-gold-hover: #b38852;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-main) !important;
            color: var(--text-primary) !important;
            letter-spacing: -0.01em;
        }

        /* Navbar Minimalist Dark */
        .navbar {
            background-color: rgba(19, 26, 38, 0.9) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border-subtle);
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.15rem;
            color: #ffffff !important;
            letter-spacing: 0.02em;
        }

        .nav-link {
            color: var(--text-muted) !important;
            font-weight: 500;
            font-size: 0.9rem;
            padding: 0.5rem 0.85rem !important;
            border-radius: 6px;
            transition: all 0.2s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: #ffffff !important;
            background-color: var(--bg-elevated);
        }

        /* Cards & Containers Override */
        .card {
            background-color: var(--bg-surface) !important;
            border: 1px solid var(--border-subtle) !important;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.28) !important;
        }

        .card-header, .card-footer {
            background-color: var(--bg-elevated) !important;
            border-color: var(--border-subtle) !important;
            color: var(--text-primary) !important;
        }

        .bg-white, .bg-light {
            background-color: var(--bg-surface) !important;
            color: var(--text-primary) !important;
        }

        .text-dark {
            color: var(--text-primary) !important;
        }

        .text-muted, .text-secondary {
            color: var(--text-muted) !important;
        }

        /* Tables Minimalist Dark */
        .table {
            --bs-table-bg: transparent;
            --bs-table-color: var(--text-primary);
            --bs-table-border-color: var(--border-subtle);
            --bs-table-hover-bg: rgba(255, 255, 255, 0.03);
            margin-bottom: 0;
        }

        .table-light, .table-dark {
            --bs-table-bg: var(--bg-elevated) !important;
            --bs-table-color: #cbd5e1 !important;
            --bs-table-border-color: var(--border-subtle) !important;
        }

        .table thead th {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 600;
            color: var(--text-muted);
            padding-top: 0.85rem;
            padding-bottom: 0.85rem;
        }

        /* Forms & Inputs */
        .form-control, .form-select {
            background-color: #0e1420 !important;
            border: 1px solid var(--border-subtle) !important;
            color: var(--text-primary) !important;
            border-radius: 8px;
            padding: 0.6rem 0.85rem;
            font-size: 0.92rem;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus, .form-select:focus {
            background-color: #0e1420 !important;
            border-color: var(--accent-gold) !important;
            box-shadow: 0 0 0 3px rgba(200, 157, 102, 0.15) !important;
            color: #ffffff !important;
        }

        .form-control::placeholder {
            color: #475569 !important;
        }

        /* Buttons */
        .btn-dark, .btn-primary {
            background-color: var(--accent-gold) !important;
            border-color: var(--accent-gold) !important;
            color: #0b0f17 !important;
            font-weight: 600;
            border-radius: 8px;
        }

        .btn-dark:hover, .btn-primary:hover {
            background-color: var(--accent-gold-hover) !important;
            border-color: var(--accent-gold-hover) !important;
            color: #0b0f17 !important;
        }

        .btn-outline-secondary, .btn-outline-light {
            border-color: var(--border-subtle) !important;
            color: var(--text-primary) !important;
            border-radius: 8px;
        }

        .btn-outline-secondary:hover, .btn-outline-light:hover {
            background-color: var(--bg-elevated) !important;
            color: #ffffff !important;
        }

        code {
            color: #e2b887 !important;
            background-color: rgba(200, 157, 102, 0.1);
            padding: 0.2em 0.45em;
            border-radius: 4px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg mb-4">
    <div class="container">
        <a class="navbar-brand" href="{{ route('dashboard') }}">Kopi Nusantara</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        @auth
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav me-auto gap-1 ms-lg-3">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.index') ? 'active' : '' }}" href="{{ route('products.index') }}">Produk</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('products.low_stock') ? 'active text-warning' : '' }}" href="{{ route('products.low_stock') }}">Stok Rendah</a></li>
                @can('admin-only')
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}" href="{{ route('categories.index') }}">Kategori</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">Supplier</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Users & Role</a></li>
                @endcan
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('purchases.*') ? 'active' : '' }}" href="{{ route('purchases.index') }}">Purchases (In)</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('sales.*') ? 'active' : '' }}" href="{{ route('sales.index') }}">Sales (Out)</a></li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <div class="text-end">
                    <div class="small fw-semibold text-white">{{ auth()->user()->name }}</div>
                    <span class="badge {{ auth()->user()->isAdmin() ? 'bg-warning text-dark' : 'bg-secondary' }}" style="font-size: 0.7rem;">
                        {{ strtoupper(auth()->user()->role) }}
                    </span>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-light btn-sm px-3">Logout</button>
                </form>
            </div>
        </div>
        @endauth
    </div>
</nav>

<div class="container pb-5">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm">
            <strong>Validasi Gagal:</strong>
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>