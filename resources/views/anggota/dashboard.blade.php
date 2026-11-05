@extends('layouts.app')

@section('title', 'Beranda Anggota')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4">Beranda Anggota</h1>
        <p>Masuk sebagai {{ auth()->user()->name }}.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Keluar</button>
        </form>
    </div>
</div>
@endsection