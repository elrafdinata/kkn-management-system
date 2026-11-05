@extends('layouts.app')

@section('title', 'Tambah Anggota')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1 class="h4 mb-0">Tambah Anggota</h1>
            <a href="{{ route('admin.members.index') }}" class="btn btn-outline-secondary btn-sm">Kembali</a>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.members.store') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Nama lengkap</label>
                        <input type="text" id="name" name="name" value="{{ old('name') }}"
                            class="form-control" required autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}"
                            class="form-control" autocomplete="off" required>
                        <div class="form-text">Huruf kecil, angka, titik, dan garis bawah. Minimal 3 karakter.</div>
                    </div>

                    <div class="mb-3">
                        <label for="schedule_category" class="form-label">Kategori jadwal</label>
                        <select id="schedule_category" name="schedule_category" class="form-select" required>
                            <option value="">Pilih kategori</option>
                            <option value="stay" @selected(old('schedule_category')==='stay' )>Stay (Senin-Minggu)</option>
                            <option value="shift_malam" @selected(old('schedule_category')==='shift_malam' )>Shift Malam (Senin-Minggu)</option>
                            <option value="weekend" @selected(old('schedule_category')==='weekend' )>Weekend (Sabtu-Minggu)</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password awal</label>
                        <input type="password" id="password" name="password"
                            class="form-control" autocomplete="new-password" required>
                        <div class="form-text">Minimal 8 karakter. Sampaikan ke anggota, lalu minta mereka menggantinya.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Simpan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection