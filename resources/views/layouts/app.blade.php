<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Beranda') - KKN Management System</title>
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>

<body class="bg-light">
    <main class="container py-4">
        @if (session('success'))
        <div class="alert alert-success" role="alert">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
        <div class="alert alert-danger" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </main>
</body>

</html>