@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4 mb-3">Laporan Absensi</h1>

        <div class="d-flex flex-wrap gap-2 mb-3">
            @foreach ($presets as $label => $range)
            <a href="{{ route('admin.reports.index', array_filter(['from' => $range[0], 'to' => $range[1], 'category' => $category, 'name' => $name])) }}"
                class="btn btn-outline-secondary btn-sm">{{ $label }}</a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.reports.index') }}" class="row g-2 mb-3">
            <div class="col-6 col-md-2">
                <label class="form-label small mb-0" for="from">Dari tanggal</label>
                <input type="date" id="from" name="from" value="{{ $from }}" class="form-control">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small mb-0" for="to">Sampai tanggal</label>
                <input type="date" id="to" name="to" value="{{ $to }}" class="form-control">
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-0" for="category">Kategori</label>
                <select id="category" name="category" class="form-select">
                    <option value="">Semua kategori</option>
                    @foreach ($categories as $key => $label)
                    <option value="{{ $key }}" @selected($category===$key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-md-3">
                <label class="form-label small mb-0" for="name">Nama anggota</label>
                <input type="text" id="name" name="name" value="{{ $name }}" class="form-control" placeholder="Cari nama">
            </div>
            <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
                <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="d-flex flex-wrap gap-2 mb-3">
            <a href="{{ route('admin.reports.excel', array_filter(['from' => $from, 'to' => $to, 'category' => $category, 'name' => $name])) }}" class="btn btn-success btn-sm">Unduh Excel</a>
            <a href="{{ route('admin.reports.pdf', array_filter(['from' => $from, 'to' => $to, 'category' => $category, 'name' => $name])) }}" class="btn btn-danger btn-sm">Unduh PDF</a>
        </div>
        <p class="text-muted small">
            Periode {{ \Carbon\Carbon::parse($from)->format('d/m/Y') }} sampai {{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}.
            Hari wajib yang sudah lewat tanpa absensi dihitung Tidak Hadir.
        </p>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Kategori</th>
                        <th class="text-end">Hari wajib</th>
                        <th class="text-end">Hadir</th>
                        <th class="text-end">Izin</th>
                        <th class="text-end">Libur</th>
                        <th class="text-end">Tidak Hadir</th>
                        <th class="text-end">Menunggu</th>
                        <th class="text-end">Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $r)
                    <tr>
                        <td>{{ $r['name'] }}</td>
                        <td>{{ $r['category'] }}</td>
                        <td class="text-end">{{ $r['required'] }}</td>
                        <td class="text-end text-success">{{ $r['hadir'] }}</td>
                        <td class="text-end text-info">{{ $r['izin'] }}</td>
                        <td class="text-end text-secondary">{{ $r['libur'] }}</td>
                        <td class="text-end text-danger">{{ $r['tidak_hadir'] }}</td>
                        <td class="text-end text-warning">{{ $r['menunggu'] }}</td>
                        <td class="text-end fw-semibold">{{ $r['percent'] !== null ? $r['percent'] . '%' : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center text-muted py-4">Tidak ada anggota yang cocok dengan filter.</td>
                    </tr>
                    @endforelse
                </tbody>
                @if (count($rows) > 0)
                <tfoot>
                    <tr class="table-light fw-semibold">
                        <td colspan="2">Total</td>
                        <td class="text-end">{{ $totals['required'] }}</td>
                        <td class="text-end">{{ $totals['hadir'] }}</td>
                        <td class="text-end">{{ $totals['izin'] }}</td>
                        <td class="text-end">{{ $totals['libur'] }}</td>
                        <td class="text-end">{{ $totals['tidak_hadir'] }}</td>
                        <td class="text-end">{{ $totals['menunggu'] }}</td>
                        <td class="text-end">{{ $totals['percent'] !== null ? $totals['percent'] . '%' : '-' }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        <div class="form-text">Kehadiran = Hadir ÷ (Hadir + Tidak Hadir). Izin, Libur, dan Menunggu tidak masuk perhitungan persentase. Hari Libur dikeluarkan dari hari wajib.</div>
    </div>
</div>
@endsection