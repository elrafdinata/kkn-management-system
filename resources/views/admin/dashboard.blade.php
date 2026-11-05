@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4">Dashboard Administrator</h1>
        <p>Masuk sebagai {{ auth()->user()->name }}.</p>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary">Keluar</button>
        </form>
    </div>
</div>
@endsection