@extends('layouts.app')

@section('title', 'Pengaturan Posko')

@section('content')
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h1 class="h4 mb-3">Pengaturan Posko</h1>

                <form method="POST" action="{{ route('admin.settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-12 col-sm-6 mb-3">
                            <label for="posko_latitude" class="form-label">Latitude</label>
                            <input type="text" inputmode="decimal" id="posko_latitude" name="posko_latitude"
                                value="{{ old('posko_latitude', $latitude) }}" class="form-control" required>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label for="posko_longitude" class="form-label">Longitude</label>
                            <input type="text" inputmode="decimal" id="posko_longitude" name="posko_longitude"
                                value="{{ old('posko_longitude', $longitude) }}" class="form-control" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 col-sm-6 mb-3">
                            <label for="geofence_radius_m" class="form-label">Radius (meter)</label>
                            <input type="text" inputmode="decimal" id="geofence_radius_m" name="geofence_radius_m"
                                value="{{ old('geofence_radius_m', $radius) }}" class="form-control" required>
                            <div class="form-text">Aturan awal: 30 meter.</div>
                        </div>
                        <div class="col-12 col-sm-6 mb-3">
                            <label for="max_gps_accuracy_m" class="form-label">Batas akurasi GPS (meter)</label>
                            <input type="text" inputmode="decimal" id="max_gps_accuracy_m" name="max_gps_accuracy_m"
                                value="{{ old('max_gps_accuracy_m', $accuracy) }}" class="form-control" required>
                            <div class="form-text">Pembacaan lebih buruk dari ini ditolak.</div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">Kembali</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection