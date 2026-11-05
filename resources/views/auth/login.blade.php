@extends('layouts.app')

@section('title', 'Masuk')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h1 class="h4 mb-1">Masuk</h1>
                <p class="text-muted mb-4">KKN Kelompok 30 UNTARA</p>

                <form method="POST" action="{{ route('login') }}" id="login-form" novalidate>
                    @csrf
                    <input type="hidden" name="latitude" id="latitude">
                    <input type="hidden" name="longitude" id="longitude">
                    <input type="hidden" name="accuracy" id="accuracy">

                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username"
                            value="{{ old('username') }}"
                            class="form-control" autocomplete="username" autofocus required>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password"
                            class="form-control" autocomplete="current-password" required>
                    </div>

                    <button type="submit" id="login-button" class="btn btn-primary w-100">Masuk</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('login-form').addEventListener('submit', function(event) {
        var form = this;
        var button = document.getElementById('login-button');

        event.preventDefault();
        button.disabled = true;
        button.textContent = 'Memeriksa lokasi...';

        var send = function() {
            form.submit();
        };

        if (!navigator.geolocation) {
            send();
            return;
        }

        navigator.geolocation.getCurrentPosition(
            function(position) {
                document.getElementById('latitude').value = position.coords.latitude;
                document.getElementById('longitude').value = position.coords.longitude;
                document.getElementById('accuracy').value = position.coords.accuracy;
                send();
            },
            send, {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            }
        );
    });
</script>
@endsection