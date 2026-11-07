@extends('layouts.app')

@section('title', 'Data Absensi')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h1 class="h4 mb-0">Data Absensi</h1>
            <a href="{{ route('admin.attendances.correct') }}" class="btn btn-outline-primary btn-sm">Koreksi / Izin</a>
        </div>

        @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="GET" action="{{ route('admin.attendances.index') }}" class="row g-2 mb-3">
            <div class="col-6 col-md-3">
                <label class="form-label small mb-0" for="from">Dari tanggal</label>
                <input type="date" id="from" name="from" value="{{ $from }}" class="form-control">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small mb-0" for="to">Sampai tanggal</label>
                <input type="date" id="to" name="to" value="{{ $to }}" class="form-control">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-0" for="name">Nama anggota</label>
                <input type="text" id="name" name="name" value="{{ $name }}" class="form-control" placeholder="Cari nama">
            </div>
            <div class="col-12 col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
                <a href="{{ route('admin.attendances.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th>Status</th>
                        <th>Jam</th>
                        <th>Jarak (m)</th>
                        <th>Akurasi (m)</th>
                        <th>Selfie</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($attendances as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row->attendance_date)->format('d/m/Y') }}</td>
                        <td>{{ $row->member_name }}</td>
                        <td>{{ $row->category_label }}</td>
                        <td>
                            @if ($row->status === 'hadir')
                            <span class="badge text-bg-success">Hadir</span>
                            @elseif ($row->status === 'izin')
                            <span class="badge text-bg-info">Izin</span>
                            @elseif ($row->status === 'libur')
                            <span class="badge text-bg-secondary">Libur</span>
                            @else
                            <span class="badge text-bg-danger">Tidak Hadir</span>
                            @endif
                            @if (in_array($row->status, ['izin', 'libur']) && $row->reason)
                            <div class="small text-muted">{{ $row->reason }}</div>
                            @endif
                        </td>
                        <td>{{ $row->attended_at ? \Carbon\Carbon::parse($row->attended_at)->timezone(config('app.timezone'))->format('H:i') : '-' }}</td>
                        <td>{{ $row->distance_m !== null ? number_format($row->distance_m, 1) : '-' }}</td>
                        <td>{{ $row->accuracy_m !== null ? number_format($row->accuracy_m, 1) : '-' }}</td>
                        <td>
                            @if ($row->selfie_path)
                            <a href="{{ route('selfie.show', $row->id) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm">Lihat</a>
                            @else
                            -
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.attendances.correct', ['member' => $row->user_id, 'date' => \Carbon\Carbon::parse($row->attendance_date)->toDateString()]) }}" class="btn btn-outline-warning btn-sm">Koreksi</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Belum ada data absensi pada rentang ini.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $attendances->links('pagination::bootstrap-5') }}

        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary mt-2">Kembali</a>
    </div>
</div>
@endsection