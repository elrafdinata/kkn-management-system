<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'latitude' => Setting::getValue('posko_latitude'),
            'longitude' => Setting::getValue('posko_longitude'),
            'radius' => Setting::getValue('geofence_radius_m', '30'),
            'accuracy' => Setting::getValue('max_gps_accuracy_m', '50'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'posko_latitude' => ['required', 'numeric', 'between:-90,90'],
            'posko_longitude' => ['required', 'numeric', 'between:-180,180'],
            'geofence_radius_m' => ['required', 'numeric', 'between:5,500'],
            'max_gps_accuracy_m' => ['required', 'numeric', 'between:5,500'],
        ], [
            'posko_latitude.required' => 'Latitude wajib diisi.',
            'posko_latitude.numeric' => 'Latitude harus berupa angka (gunakan titik, bukan koma).',
            'posko_latitude.between' => 'Latitude harus antara -90 dan 90.',
            'posko_longitude.required' => 'Longitude wajib diisi.',
            'posko_longitude.numeric' => 'Longitude harus berupa angka (gunakan titik, bukan koma).',
            'posko_longitude.between' => 'Longitude harus antara -180 dan 180.',
            'geofence_radius_m.required' => 'Radius wajib diisi.',
            'geofence_radius_m.numeric' => 'Radius harus berupa angka.',
            'geofence_radius_m.between' => 'Radius harus antara 5 dan 500 meter.',
            'max_gps_accuracy_m.required' => 'Batas akurasi wajib diisi.',
            'max_gps_accuracy_m.numeric' => 'Batas akurasi harus berupa angka.',
            'max_gps_accuracy_m.between' => 'Batas akurasi harus antara 5 dan 500 meter.',
        ]);

        $changes = [];

        foreach ($data as $key => $value) {
            $old = Setting::getValue($key);
            $new = (string) $value;

            if ($old !== $new) {
                Setting::updateOrCreate(['key' => $key], ['value' => $new]);
                $changes[] = "{$key}: {$old} -> {$new}";
            }
        }

        if ($changes) {
            ActivityLog::record(
                $request,
                'ubah_pengaturan_posko',
                'Mengubah pengaturan posko (' . implode('; ', $changes) . ')'
            );

            return redirect()->route('admin.settings.edit')
                ->with('success', 'Pengaturan posko disimpan.');
        }

        return redirect()->route('admin.settings.edit')
            ->with('success', 'Tidak ada perubahan.');
    }
}
