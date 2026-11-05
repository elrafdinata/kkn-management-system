@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4">Dashboard Administrator</h1>
        <p>Masuk sebagai {{ auth()->user()->name }}.</p>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.members.index') }}" class="btn btn-primary">Kelola Anggota</a>
            <a href="{{ route('admin.logs.index') }}" class="btn btn-outline-primary">Riwayat Aktivitas</a>
            <a href="{{ route('admin.qr.show') }}" class="btn btn-outline-primary">QR Absensi</a>
            <a href="{{ route('password.edit') }}" class="btn btn-outline-primary">Ganti Password</a>
            <a href="{{ route('admin.settings.edit') }}" class="btn btn-outline-primary">Pengaturan Posko</a>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Keluar</button>
        </form>
    </div>
</div>
@endsection