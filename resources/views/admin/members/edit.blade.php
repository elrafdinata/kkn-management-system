@extends('layouts.app')

@section('title', 'Ubah Anggota')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Ubah Anggota</h1>
            <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body p-4">
                <h2 class="h6 mb-3">Data anggota</h2>
                <form method="POST" action="{{ route('admin.members.update', $member) }}" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" value="{{ $member->username }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama lengkap</label>
                        <input type="text" id="name" name="name"
                            value="{{ old('name', $member->name) }}" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="schedule_category" class="form-label">Kategori jadwal</label>
                        <select id="schedule_category" name="schedule_category" class="form-select" required>
                            @php($current = old('schedule_category', $member->schedule_category))
                            <option value="stay" @selected($current==='stay' )>Stay (Senin-Minggu)</option>
                            <option value="shift_malam" @selected($current==='shift_malam' )>Shift Malam (Senin-Minggu)</option>
                            <option value="weekend" @selected($current==='weekend' )>Weekend (Sabtu-Minggu)</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Simpan perubahan</button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body p-4">
                <h2 class="h6 mb-3">Status akun</h2>
                <p class="mb-3">
                    Saat ini:
                    @if ($member->is_active)
                    <span class="badge text-bg-success">Aktif</span>
                    @else
                    <span class="badge text-bg-secondary">Nonaktif</span>
                    @endif
                </p>
                <form method="POST" action="{{ route('admin.members.status', $member) }}"
                    onsubmit="return confirm('Yakin mengubah status akun ini?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit"
                        class="btn {{ $member->is_active ? 'btn-outline-danger' : 'btn-outline-success' }} w-100">
                        {{ $member->is_active ? 'Nonaktifkan akun' : 'Aktifkan akun' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h2 class="h6 mb-3">Reset password</h2>
                <form method="POST" action="{{ route('admin.members.password', $member) }}" novalidate>
                    @csrf
                    @method('PATCH')

                    <div class="mb-3">
                        <label for="password" class="form-label">Password baru</label>
                        <input type="password" id="password" name="password"
                            class="form-control" autocomplete="new-password" required>
                        <div class="form-text">Minimal 8 karakter. Anggota akan keluar dari semua perangkat.</div>
                    </div>

                    <button type="submit" class="btn btn-outline-primary w-100">Reset password</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection