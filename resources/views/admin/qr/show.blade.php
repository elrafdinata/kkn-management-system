@extends('layouts.app')

@section('title', 'QR Absensi')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-1">QR Absensi</h1>
                <p class="text-muted">{{ $todayText }}</p>

                <p class="mb-3">
                    Status hari ini:
                    @if (! $session)
                    <span class="badge text-bg-secondary">Belum dibuat</span>
                    @elseif ($session->revoked_at)
                    <span class="badge text-bg-danger">Dicabut</span>
                    @else
                    <span class="badge text-bg-success">Aktif</span>
                    <span class="text-muted small">sampai {{ $session->expires_at->timezone(config('app.timezone'))->format('H:i') }} WIB</span>
                    @endif
                </p>

                @if ($svg)
                <div class="text-center mb-3">
                    {!! $svg !!}
                    <div class="form-text">QR hanya tampil sekali. Jika halaman ini ditutup atau dimuat ulang, buat ulang QR.</div>
                </div>
                @endif

                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('admin.qr.store') }}"
                        @if ($session) onsubmit="return confirm('QR lama akan tidak berlaku. Lanjutkan?')" @endif>
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            {{ $session ? 'Buat ulang QR' : 'Buat QR hari ini' }}
                        </button>
                    </form>

                    @if ($session && ! $session->revoked_at)
                    <form method="POST" action="{{ route('admin.qr.revoke') }}"
                        onsubmit="return confirm('Cabut QR hari ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger">Cabut QR</button>
                    </form>
                    @endif

                    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Kembali</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection