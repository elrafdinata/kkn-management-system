@extends('layouts.app')

@section('title', 'Koreksi Absensi')

@section('content')
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h4 mb-3">Koreksi Absensi</h1>

        <p class="text-muted small">
            Koreksi dan Izin dicatat di riwayat aktivitas beserta alasannya. Absensi baru hasil koreksi tidak memiliki selfie dan lokasi.
            Izin boleh untuk tanggal ke depan, tetapi hanya pada hari wajib anggota tersebut.
        </p>

        @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('admin.attendances.correct.store') }}" class="row g-3">
            @csrf

            <div class="col-12 col-md-6">
                <label class="form-label" for="member_id">Anggota</label>
                <select id="member_id" name="member_id" class="form-select" required>
                    <option value="">Pilih anggota</option>
                    @foreach ($members as $m)
                    <option value="{{ $m->id }}" @selected((int) old('member_id', $memberId)===$m->id)>
                        {{ $m->name }} ({{ \App\Services\ScheduleService::CATEGORIES[$m->schedule_category] ?? '-' }})
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label" for="date">Tanggal</label>
                <input type="date" id="date" name="date" value="{{ old('date', $date) }}" class="form-control" required>
            </div>

            <div class="col-12 col-md-3">
                <label class="form-label" for="status">Status baru</label>
                <select id="status" name="status" class="form-select" required>
                    <option value="">Pilih status</option>
                    <option value="hadir" @selected(old('status')==='hadir' )>Hadir</option>
                    <option value="tidak_hadir" @selected(old('status')==='tidak_hadir' )>Tidak Hadir</option>
                    <option value="izin" @selected(old('status')==='izin' )>Izin</option>
                    <option value="libur" @selected(old('status')==='libur' )>Libur</option>
                </select>
            </div>

            <div class="col-12">
                <label class="form-label" for="reason">Alasan</label>
                <textarea id="reason" name="reason" rows="3" maxlength="255" class="form-control" required>{{ old('reason') }}</textarea>
                <div class="form-text">Wajib diisi, 5 sampai 255 karakter.</div>
            </div>

            <div class="col-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('admin.attendances.index') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection