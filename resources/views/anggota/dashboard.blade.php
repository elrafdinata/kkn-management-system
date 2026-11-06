@extends('layouts.app')

@section('title', 'Beranda Anggota')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4">Beranda Anggota</h1>
        <p>Masuk sebagai {{ auth()->user()->name }}.</p>

        <dl class="row mb-3">
            <dt class="col-5 col-sm-3">Kategori</dt>
            <dd class="col-7 col-sm-9">{{ $categoryLabel }}</dd>

            <dt class="col-5 col-sm-3">Hari ini</dt>
            <dd class="col-7 col-sm-9">{{ $todayText }}</dd>

            <dt class="col-5 col-sm-3">Status</dt>
            <dd class="col-7 col-sm-9">
                @if ($attendanceStatus === 'hadir')
                <span class="badge text-bg-success">Hadir</span>
                <span class="text-muted small">pukul {{ $attendedTime }} WIB</span>
                @elseif ($attendanceStatus === 'tidak_hadir')
                <span class="badge text-bg-danger">Tidak Hadir</span>
                @elseif ($attendanceStatus === 'menunggu')
                <span class="badge text-bg-warning">Menunggu</span>
                <div class="text-muted small mt-1">Hari wajib. Ambil selfie dan kirim lokasi untuk absen.</div>
                <a href="{{ route('anggota.absen') }}" class="btn btn-primary btn-sm mt-2">Absen Sekarang</a>
                @else
                <span class="badge text-bg-secondary">Bukan hari wajib</span>
                @endif
            </dd>
        </dl>

        <a href="{{ route('password.edit') }}" class="btn btn-outline-primary mb-3">Ganti Password</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Keluar</button>
        </form>
    </div>
</div>
@endsection