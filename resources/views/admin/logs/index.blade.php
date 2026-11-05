@extends('layouts.app')

@section('title', 'Riwayat Aktivitas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Riwayat Aktivitas</h1>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Waktu</th>
                    <th>Pelaku</th>
                    <th>Aksi</th>
                    <th>Keterangan</th>
                    <th>IP</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                <tr>
                    <td class="text-nowrap">{{ $log->created_at?->format('d-m-Y H:i:s') }}</td>
                    <td>{{ $log->user?->name ?? '-' }}</td>
                    <td><span class="badge text-bg-secondary">{{ $log->action }}</span></td>
                    <td>{{ $log->description ?? '-' }}</td>
                    <td class="text-nowrap">{{ $log->ip_address ?? '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Belum ada aktivitas tercatat.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $logs->links() }}
</div>
@endsection