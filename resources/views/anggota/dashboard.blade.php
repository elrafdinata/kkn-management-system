@extends('layouts.app')

@section('title', 'Beranda Anggota')

@section('content')
<div class="mb-3">
    <h1 class="h4 mb-0">Halo, {{ auth()->user()->name }}</h1>
    <p class="text-muted mb-0">{{ $todayText }} &middot; {{ $categoryLabel }}</p>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="text-muted small mb-1">Status absensi hari ini</div>
        @if ($attendanceStatus === 'hadir')
        <span class="badge text-bg-success fs-6">Hadir</span>
        @if ($attendedTime)
        <span class="text-muted small ms-1">pukul {{ $attendedTime }} WIB</span>
        @endif
        @elseif ($attendanceStatus === 'tidak_hadir')
        <span class="badge text-bg-danger fs-6">Tidak Hadir</span>
        @elseif ($attendanceStatus === 'menunggu')
        <span class="badge text-bg-warning fs-6">Menunggu</span>
        <div class="text-muted small mt-2">Hari wajib. Ambil selfie dan kirim lokasi untuk absen.</div>
        <a href="{{ route('anggota.absen') }}" class="btn btn-primary btn-lg w-100 mt-3">Absen Sekarang</a>
        @else
        <span class="badge text-bg-secondary fs-6">Bukan hari wajib</span>
        @endif
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-4">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center px-2">
                <div class="text-muted small">Hadir</div>
                <div class="h3 mb-0 text-success">{{ $hadirCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center px-2">
                <div class="text-muted small">Tidak Hadir</div>
                <div class="h3 mb-0 text-danger">{{ $tidakHadirCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card shadow-sm h-100">
            <div class="card-body text-center px-2">
                <div class="text-muted small">Kehadiran</div>
                <div class="h3 mb-0">{{ $percent !== null ? $percent . '%' : '-' }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <h2 class="h6">Riwayat 10 hari terakhir</h2>
        @if ($history->isEmpty())
        <p class="text-muted mb-0">Belum ada riwayat absensi.</p>
        @else
        <ul class="list-group list-group-flush">
            @foreach ($history as $row)
            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                <span>{{ \Carbon\Carbon::parse($row->attendance_date)->format('d/m/Y') }}</span>
                <span>
                    @if ($row->status === 'hadir')
                    @if ($row->attended_at)
                    <span class="text-muted small me-1">{{ \Carbon\Carbon::parse($row->attended_at)->timezone(config('app.timezone'))->format('H:i') }}</span>
                    @endif
                    <span class="badge text-bg-success">Hadir</span>
                    @else
                    <span class="badge text-bg-danger">Tidak Hadir</span>
                    @endif
                </span>
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>
@endsection