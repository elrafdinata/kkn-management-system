<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#5E6040">
    <title>@yield('title', 'Beranda') - KKN Management System</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo-karaksa30.jpg') }}">
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body>
    @php
    $isAdminArea = request()->routeIs('admin.*');
    @endphp

    <header class="app-header shadow-sm">
        <div class="container py-2">
            <div class="d-flex align-items-center justify-content-between gap-2">
                <a href="{{ auth()->check() ? ($isAdminArea ? route('admin.dashboard') : route('anggota.dashboard')) : route('login') }}"
                    class="app-brand">
                    <img src="{{ asset('images/logo-karaksa30.jpg') }}" alt="Logo Karaksa Kelompok 30">
                    <span class="brand-title">
                        KKN Management System
                        <small class="brand-sub">Karaksa &middot; Kelompok 30 UNTARA</small>
                    </span>
                </a>

                @auth
                {{-- Laptop/tablet: tombol Keluar di header --}}
                <form method="POST" action="{{ route('logout') }}" class="m-0 d-none d-md-block">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">Keluar</button>
                </form>

                {{-- HP: tombol burger --}}
                <button type="button" id="menuToggle" class="menu-toggle d-md-none" aria-label="Buka menu"
                    aria-expanded="false" aria-controls="menuDrawer">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                        stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                        <path d="M4 7h16M4 12h16M4 17h16" />
                    </svg>
                </button>
                @endauth
            </div>

            @auth
            {{-- Laptop/tablet: menu horizontal --}}
            <nav class="nav app-nav mt-2 gap-1 flex-wrap d-none d-md-flex" aria-label="Navigasi utama">
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

    @auth
    <div id="menuOverlay" class="menu-overlay"></div>

    <aside id="menuDrawer" class="menu-drawer" aria-label="Menu utama" aria-hidden="true">
        <div class="menu-drawer-head">
            <div class="menu-user">
                <div class="menu-user-name">{{ auth()->user()->name }}</div>
                <span class="menu-user-role">{{ auth()->user()->role === 'admin' ? 'Administrator' : 'Anggota' }}</span>
            </div>
            <button type="button" id="menuClose" class="menu-close" aria-label="Tutup menu">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                    stroke-width="2.2" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" />
                </svg>
            </button>
        </div>

        <nav class="menu-links" aria-label="Navigasi HP">
            @if ($isAdminArea)
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('admin.attendances.*') ? 'active' : '' }}" href="{{ route('admin.attendances.index') }}">Absensi</a>
            <a class="{{ request()->routeIs('admin.members.*') ? 'active' : '' }}" href="{{ route('admin.members.index') }}">Anggota</a>
            <a class="{{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}">Laporan</a>
            <a class="{{ request()->routeIs('admin.qr.*') ? 'active' : '' }}" href="{{ route('admin.qr.show') }}">QR</a>
            <a class="{{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}">Pengaturan Posko</a>
            <a class="{{ request()->routeIs('admin.logs.*') ? 'active' : '' }}" href="{{ route('admin.logs.index') }}">Riwayat Aktivitas</a>
            @else
            <a class="{{ request()->routeIs('anggota.dashboard') ? 'active' : '' }}" href="{{ route('anggota.dashboard') }}">Beranda</a>
            <a class="{{ request()->routeIs('anggota.absen') ? 'active' : '' }}" href="{{ route('anggota.absen') }}">Absen</a>
            <a class="{{ request()->routeIs('password.*') ? 'active' : '' }}" href="{{ route('password.edit') }}">Ganti Password</a>
            @endif
        </nav>

        <div class="menu-foot">
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-danger w-100">Keluar</button>
            </form>
        </div>
    </aside>
    @endauth

    @if (session()->has('impersonator_id') && auth()->check())
    <div class="impersonation-bar">
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

    @auth
    <script>
        (function() {
            var toggle = document.getElementById('menuToggle');
            var drawer = document.getElementById('menuDrawer');
            var overlay = document.getElementById('menuOverlay');
            var closeBtn = document.getElementById('menuClose');
            if (!toggle || !drawer || !overlay) {
                return;
            }

            function setOpen(open) {
                drawer.classList.toggle('open', open);
                overlay.classList.toggle('open', open);
                document.body.classList.toggle('menu-open', open);
                toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
                drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
                if (open && closeBtn) {
                    closeBtn.focus();
                } else if (!open) {
                    toggle.focus();
                }
            }

            toggle.addEventListener('click', function() {
                setOpen(true);
            });
            overlay.addEventListener('click', function() {
                setOpen(false);
            });
            if (closeBtn) {
                closeBtn.addEventListener('click', function() {
                    setOpen(false);
                });
            }
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && drawer.classList.contains('open')) {
                    setOpen(false);
                }
            });
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768 && drawer.classList.contains('open')) {
                    setOpen(false);
                }
            });
        })();
    </script>
    @endauth
</body>

</html>