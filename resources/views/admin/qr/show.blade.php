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
                    <div id="qrBox">{!! $svg !!}</div>
                    <div class="mt-3">
                        <button type="button" id="unduhQr" class="btn btn-success">Unduh QR (PNG)</button>
                    </div>
                    <div class="form-text mt-2">
                        Unduh QR sekarang. Jika halaman ini ditutup atau dimuat ulang, QR tidak bisa ditampilkan lagi.
                        QR yang sudah diunduh tetap berlaku sampai akhir hari, walaupun admin logout.
                    </div>
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

@if ($svg)
<script>
    document.getElementById('unduhQr').addEventListener('click', function() {
        var asli = document.querySelector('#qrBox svg');
        if (!asli) {
            alert('QR tidak ditemukan di halaman.');
            return;
        }

        var size = 800,
            pad = 40;
        var svg = asli.cloneNode(true);
        svg.setAttribute('width', size);
        svg.setAttribute('height', size);
        var xml = new XMLSerializer().serializeToString(svg);

        var img = new Image();
        img.onload = function() {
            var c = document.createElement('canvas');
            c.width = size + pad * 2;
            c.height = size + pad * 2;
            var ctx = c.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, c.width, c.height);
            ctx.drawImage(img, pad, pad, size, size);
            var a = document.createElement('a');
            a.href = c.toDataURL('image/png');
            a.download = 'qr-absensi-' + new Date().toISOString().slice(0, 10) + '.png';
            a.click();
        };
        img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(xml);
    });
</script>
@endif
@endsection