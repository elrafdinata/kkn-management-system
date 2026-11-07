@extends('layouts.app')

@section('title', 'Daftar Anggota')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Daftar Anggota</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.members.create') }}" class="btn btn-primary btn-sm">Tambah Anggota</a>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama</th>
                    <th>Username</th>
                    <th>Kategori</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse ($members as $member)
                <tr>
                    <td>{{ $member->name }}</td>
                    <td>{{ $member->username }}</td>
                    <td>
                        {{ match ($member->schedule_category) {
                                    'stay' => 'Stay',
                                    'shift_malam' => 'Shift Malam',
                                    'weekend' => 'Weekend',
                                    default => '-',
                                } }}
                    </td>
                    <td>
                        @if ($member->is_active)
                        <span class="badge text-bg-success">Aktif</span>
                        @else
                        <span class="badge text-bg-secondary">Nonaktif</span>
                        @endif
                    </td>
                    <td class="text-end text-nowrap">
                        @if ($member->is_active)
                        <form method="POST" action="{{ route('admin.members.impersonate', $member) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary btn-sm">Masuk sebagai</button>
                        </form>
                        @endif
                        <a href="{{ route('admin.members.edit', $member) }}"
                            class="btn btn-outline-primary btn-sm">Ubah</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">Belum ada anggota.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $members->links() }}
</div>
@endsection