@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="mb-3">
    <h1 class="h4 mb-0">Dashboard Administrator</h1>
    <p class="text-muted mb-0">Halo, {{ auth()->user()->name }}. Ringkasan absensi {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}.</p>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Anggota aktif</div>
                <div class="display-6 fw-semibold">{{ $totalMembers }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Wajib hadir hari ini</div>
                <div class="display-6 fw-semibold">{{ $requiredCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm h-100 border-success">
            <div class="card-body">
                <div class="text-muted small">Sudah hadir</div>
                <div class="display-6 fw-semibold text-success">{{ $hadir }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm h-100 border-warning">
            <div class="card-body">
                <div class="text-muted small">Belum absen (menunggu)</div>
                <div class="display-6 fw-semibold text-warning">{{ $waiting->count() }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-8">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6">Belum absen hari ini</h2>
                @if ($waiting->isEmpty())
                <p class="text-muted mb-0">Tidak ada anggota yang menunggu.</p>
                @else
                <ul class="list-group list-group-flush">
                    @foreach ($waiting as $item)
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>{{ $item['name'] }}</span>
                        <span class="badge text-bg-secondary">{{ $item['category'] }}</span>
                    </li>
                    @endforeach
                </ul>
                @endif
                @if ($tidakHadir > 0)
                <p class="small text-muted mt-3 mb-0">Tercatat Tidak Hadir hari ini: {{ $tidakHadir }} anggota.</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-grid gap-2 align-content-start">
                <h2 class="h6">Aksi cepat</h2>
                <a href="{{ route('admin.attendances.index') }}" class="btn btn-primary">Data Absensi</a>
                <a href="{{ route('admin.attendances.correct') }}" class="btn btn-outline-primary">Koreksi Absensi</a>
                <a href="{{ route('admin.qr.show') }}" class="btn btn-outline-primary">QR Absensi</a>
                <a href="{{ route('admin.members.index') }}" class="btn btn-outline-primary">Kelola Anggota</a>
            </div>
        </div>
    </div>
</div>
@endsection