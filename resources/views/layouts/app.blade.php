<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') - KKN Management System</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    <style>
        .app-navbar .nav-link {
            border-radius: .5rem;
            padding: .4rem .75rem;
            color: #495057;
        }

        .app-navbar .nav-link:hover {
            background: #e9ecef;
        }

        .app-navbar .nav-link.active {
            background: #0d6efd;
            color: #fff;
        }

        .app-brand small {
            font-weight: 400;
        }
    </style>
</head>

<body class="bg-light">
    @php
    $isAdminArea = request()->routeIs('admin.*');
    @endphp

    <header class="bg-white border-bottom shadow-sm app-navbar">
        <div class="container py-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                <a href="{{ auth()->check() ? ($isAdminArea ? route('admin.dashboard') : route('anggota.dashboard')) : route('login') }}"
                    class="text-decoration-none text-dark fw-bold app-brand">
                    KKN Management System
                    <small class="text-muted d-block d-sm-inline ms-sm-1">Kelompok 30 UNTARA</small>
                </a>

                @auth
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">Keluar</button>
                </form>
                @endauth
            </div>

            @auth
            <nav class="nav mt-2 gap-1 flex-wrap" aria-label="Navigasi utama">
                @if ($isAdminArea)
                <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
                <a class="nav-link {{ request()->routeIs('admin.attendances.*') ? 'active' : '' }}" href="{{ route('admin.attendances.index') }}">Absensi</a>
                <a class="nav-link {{ request()->routeIs('admin.members.*') ? 'active' : '' }}" href="{{ route('admin.members.index') }}">Anggota</a>
                <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">Laporan</a>
                <a class="nav-link {{ request()->routeIs('admin.qr.*') ? 'active' : '' }}" href="{{ route('admin.qr.show') }}">QR</a>
                <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Pengaturan Posko</a>
                <a class="nav-link {{ request()->routeIs('admin.logs.*') ? 'active' : '' }}" href="{{ route('admin.logs.index') }}">Riwayat Aktivitas</a>
                @else
                <a class="nav-link {{ request()->routeIs('anggota.dashboard') ? 'active' : '' }}" href="{{ route('anggota.dashboard') }}">Beranda</a>
                <a class="nav-link {{ request()->routeIs('anggota.absen') ? 'active' : '' }}" href="{{ route('anggota.absen') }}">Absen</a>
                <a class="nav-link {{ request()->routeIs('password.*') ? 'active' : '' }}" href="{{ route('password.edit') }}">Ganti Password</a>
                @endif
            </nav>
            @endauth
        </div>
    </header>

    @if (session()->has('impersonator_id') && auth()->check())
    <div class="bg-warning-subtle border-bottom">
        <div class="container py-2 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <span class="small">
                Mode lihat tampilan: Anda masuk sebagai <strong>{{ auth()->user()->name }}</strong>.
                Kirim absensi dan ganti password dinonaktifkan.
            </span>
            <form method="POST" action="{{ route('impersonate.stop') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-dark btn-sm">Kembali ke admin</button>
            </form>
        </div>
    </div>
    @endif

    <main class="container py-4">
        @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </main>
</body>

</html>