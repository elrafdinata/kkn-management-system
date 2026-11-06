@extends('layouts.app')

@section('title', 'Absensi')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4">Absensi</h1>

        <div class="alert alert-{{ $type }}" role="alert">
            {{ $message }}
        </div>

        @if (! empty($token))
        <form method="POST" action="{{ route('anggota.absen.lokasi') }}" id="absen-form" enctype="multipart/form-data" class="mb-3">
            @csrf
            <input type="hidden" name="t" value="{{ $token }}">
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">
            <input type="hidden" name="accuracy" id="accuracy">
            <input type="file" name="selfie" id="selfie-input" accept="image/jpeg" hidden>

            <div class="mb-3">
                <video id="camera" class="w-100 rounded border" autoplay playsinline muted hidden></video>
                <canvas id="canvas" hidden></canvas>
                <img id="preview" class="w-100 rounded border" alt="Pratinjau selfie" hidden>
            </div>

            <div id="camera-error" class="text-danger mb-2" hidden></div>
            <div id="gps-error" class="text-danger mb-2" hidden></div>

            <div class="d-flex flex-wrap gap-2">
                <button type="button" id="open-camera" class="btn btn-outline-primary">Buka Kamera</button>
                <button type="button" id="take-photo" class="btn btn-primary" hidden>Ambil Foto</button>
                <button type="button" id="retake" class="btn btn-outline-secondary" hidden>Ambil Ulang</button>
                <button type="submit" id="absen-button" class="btn btn-success" disabled>Kirim Absen</button>
            </div>
        </form>
        @endif

        <a href="{{ route('anggota.dashboard') }}" class="btn btn-outline-secondary">Kembali ke Beranda</a>
    </div>
</div>

@if (! empty($token))
<script>
    (function() {
        var form = document.getElementById('absen-form');
        var video = document.getElementById('camera');
        var canvas = document.getElementById('canvas');
        var preview = document.getElementById('preview');
        var input = document.getElementById('selfie-input');
        var openBtn = document.getElementById('open-camera');
        var takeBtn = document.getElementById('take-photo');
        var retakeBtn = document.getElementById('retake');
        var sendBtn = document.getElementById('absen-button');
        var camError = document.getElementById('camera-error');
        var gpsError = document.getElementById('gps-error');
        var stream = null;

        function showCameraError(text) {
            camError.textContent = text;
            camError.hidden = false;
        }

        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(function(track) {
                    track.stop();
                });
                stream = null;
            }
            video.srcObject = null;
            video.hidden = true;
        }

        openBtn.addEventListener('click', function() {
            camError.hidden = true;

            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                showCameraError('Browser ini tidak mendukung kamera. Gunakan browser lain.');
                return;
            }

            navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user'
                },
                audio: false
            }).then(function(s) {
                stream = s;
                video.srcObject = s;
                video.hidden = false;
                preview.hidden = true;
                openBtn.hidden = true;
                retakeBtn.hidden = true;
                takeBtn.hidden = false;
            }).catch(function(error) {
                if (error && error.name === 'NotAllowedError') {
                    showCameraError('Izin kamera ditolak. Izinkan akses kamera di pengaturan browser, lalu coba lagi.');
                } else {
                    showCameraError('Kamera tidak tersedia. Pastikan kamera berfungsi dan tidak dipakai aplikasi lain.');
                }
            });
        });

        takeBtn.addEventListener('click', function() {
            var w = video.videoWidth;
            var h = video.videoHeight;

            if (!w || !h) {
                showCameraError('Kamera belum siap. Tunggu sebentar, lalu coba lagi.');
                return;
            }

            camError.hidden = true;

            var scale = Math.min(1, 720 / w);
            canvas.width = Math.round(w * scale);
            canvas.height = Math.round(h * scale);
            canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);

            canvas.toBlob(function(blob) {
                if (!blob) {
                    showCameraError('Gagal mengambil foto. Coba lagi.');
                    return;
                }

                try {
                    var file = new File([blob], 'selfie.jpg', {
                        type: 'image/jpeg'
                    });
                    var transfer = new DataTransfer();
                    transfer.items.add(file);
                    input.files = transfer.files;
                } catch (error) {
                    showCameraError('Browser ini tidak dapat mengirim foto. Perbarui browser Anda.');
                    return;
                }

                preview.src = URL.createObjectURL(blob);
                preview.hidden = false;
                stopCamera();
                takeBtn.hidden = true;
                retakeBtn.hidden = false;
                sendBtn.disabled = false;
            }, 'image/jpeg', 0.85);
        });

        retakeBtn.addEventListener('click', function() {
            input.value = '';
            preview.hidden = true;
            sendBtn.disabled = true;
            retakeBtn.hidden = true;
            openBtn.click();
        });

        form.addEventListener('submit', function(event) {
            event.preventDefault();
            gpsError.hidden = true;

            var fail = function(text) {
                gpsError.textContent = text;
                gpsError.hidden = false;
                sendBtn.disabled = false;
                sendBtn.textContent = 'Kirim Absen';
            };

            if (!input.files || input.files.length === 0) {
                fail('Ambil selfie dari kamera terlebih dahulu.');
                return;
            }

            if (!navigator.geolocation) {
                fail('Browser ini tidak mendukung lokasi. Gunakan browser lain.');
                return;
            }

            sendBtn.disabled = true;
            sendBtn.textContent = 'Memeriksa lokasi...';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    document.getElementById('latitude').value = position.coords.latitude;
                    document.getElementById('longitude').value = position.coords.longitude;
                    document.getElementById('accuracy').value = position.coords.accuracy;
                    form.submit();
                },
                function(error) {
                    if (error.code === 1) {
                        fail('Izin lokasi ditolak. Izinkan akses lokasi di pengaturan browser, lalu coba lagi.');
                    } else {
                        fail('Lokasi tidak tersedia. Aktifkan GPS dan coba lagi di tempat terbuka.');
                    }
                }, {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 0
                }
            );
        });
    })();
</script>
@endif
@endsection