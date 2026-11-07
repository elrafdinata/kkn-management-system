@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="mb-3">
    <h1 class="h4 mb-0">Dashboard Administrator</h1>
    <p class="text-muted mb-0">Halo, {{ auth()->user()->name }}. Ringkasan absensi {{ \Carbon\Carbon::parse($today)->format('d/m/Y') }}.</p>
</div>

<div class="row row-cols-2 row-cols-md-5 g-3 mb-3">
    <div class="col">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <div class="text-muted small">Wajib hadir hari ini</div>
                <div class="display-6 fw-semibold">{{ $requiredCount }}</div>
                <div class="text-muted small">dari {{ $totalMembers }} anggota aktif{{ $libur > 0 ? ', ' . $libur . ' libur' : '' }}</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm h-100 border-success">
            <div class="card-body">
                <div class="text-muted small">Hadir</div>
                <div class="display-6 fw-semibold text-success">{{ $hadir }}</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm h-100 border-info">
            <div class="card-body">
                <div class="text-muted small">Izin</div>
                <div class="display-6 fw-semibold text-info">{{ $izin }}</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm h-100 border-warning">
            <div class="card-body">
                <div class="text-muted small">Menunggu</div>
                <div class="display-6 fw-semibold text-warning">{{ $waiting->count() }}</div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card shadow-sm h-100 border-danger">
            <div class="card-body">
                <div class="text-muted small">Tidak Hadir</div>
                <div class="display-6 fw-semibold text-danger">{{ $tidakHadir }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
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
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card shadow-sm h-100">
            <div class="card-body d-grid gap-2 align-content-start">
                <h2 class="h6">Aksi cepat</h2>
                <a href="{{ route('admin.attendances.index') }}" class="btn btn-primary">Data Absensi</a>
                <a href="{{ route('admin.attendances.correct') }}" class="btn btn-outline-primary">Koreksi / Izin</a>
                <a href="{{ route('admin.qr.show') }}" class="btn btn-outline-primary">QR Absensi</a>
                <a href="{{ route('admin.members.index') }}" class="btn btn-outline-primary">Kelola Anggota</a>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <h2 class="h6">Kehadiran 7 hari terakhir</h2>
        <div class="small text-muted mb-2">
            <span class="badge text-bg-success">Hadir</span>
            <span class="badge text-bg-info">Izin</span>
            <span class="badge text-bg-warning">Menunggu (hari ini)</span>
            <span class="badge text-bg-danger">Tidak Hadir</span>
        </div>
        @foreach ($trend as $day)
        @php
        $total = max($day['required'], 1);
        @endphp
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="small text-nowrap" style="width: 5.5rem;">{{ $day['label'] }}</div>
            <div class="progress flex-grow-1" style="height: 1.25rem;">
                <div class="progress-bar bg-success" style="width: {{ $day['hadir'] / $total * 100 }}%"></div>
                <div class="progress-bar bg-info" style="width: {{ $day['izin'] / $total * 100 }}%"></div>
                <div class="progress-bar bg-warning" style="width: {{ $day['waiting'] / $total * 100 }}%"></div>
                <div class="progress-bar bg-danger" style="width: {{ $day['missed'] / $total * 100 }}%"></div>
            </div>
            <div class="small text-nowrap text-end" style="width: 4.5rem;">{{ $day['hadir'] }}/{{ $day['required'] }}</div>
        </div>
        @endforeach
        <div class="form-text">Angka di kanan: hadir dari jumlah anggota yang wajib pada hari itu.</div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-lg-7">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6">Kehadiran per kategori ({{ $windowDays }} hari terakhir)</h2>
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Kategori</th>
                                <th class="text-end">Anggota</th>
                                <th class="text-end">Hadir</th>
                                <th class="text-end">Izin</th>
                                <th class="text-end">Tidak Hadir</th>
                                <th class="text-end">Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($categoryStats as $c)
                            <tr>
                                <td>{{ $c['label'] }}</td>
                                <td class="text-end">{{ $c['members'] }}</td>
                                <td class="text-end">{{ $c['hadir'] }}</td>
                                <td class="text-end">{{ $c['izin'] }}</td>
                                <td class="text-end">{{ $c['missed'] }}</td>
                                <td class="text-end fw-semibold">{{ $c['percent'] !== null ? $c['percent'] . '%' : '-' }}</td>
                            </tr>
                            @endforeach
                            <tr class="table-light">
                                <td class="fw-semibold" colspan="5">Keseluruhan</td>
                                <td class="text-end fw-semibold">{{ $overallPercent !== null ? $overallPercent . '%' : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="form-text mt-2">Kehadiran = Hadir ÷ (hari wajib − Izin). Hari ini tidak dihitung karena belum berakhir.</div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card shadow-sm h-100">
            <div class="card-body">
                <h2 class="h6">Tidak Hadir terbanyak ({{ $windowDays }} hari)</h2>
                @if ($topMissed->isEmpty())
                <p class="text-muted mb-0">Belum ada anggota dengan Tidak Hadir.</p>
                @else
                <ul class="list-group list-group-flush">
                    @foreach ($topMissed as $item)
                    <li class="list-group-item d-flex justify-content-between px-0">
                        <span>{{ $item['name'] }}</span>
                        <span class="badge text-bg-danger">{{ $item['missed'] }} hari</span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection