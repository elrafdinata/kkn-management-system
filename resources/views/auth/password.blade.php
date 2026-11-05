@extends('layouts.app')

@section('title', 'Ganti Password')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Ganti Password</h1>

                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="current_password" class="form-label">Password lama</label>
                        <input type="password" id="current_password" name="current_password"
                            class="form-control" autocomplete="current-password" required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password baru</label>
                        <input type="password" id="password" name="password"
                            class="form-control" autocomplete="new-password" minlength="8" required>
                        <div class="form-text">Minimal 8 karakter.</div>
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Ulangi password baru</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control" autocomplete="new-password" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ auth()->user()->role === 'admin' ? route('admin.dashboard') : route('anggota.dashboard') }}"
                            class="btn btn-outline-secondary">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection