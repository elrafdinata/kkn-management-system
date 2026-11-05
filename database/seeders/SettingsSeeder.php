<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $posko = config('kkn.posko');

        if ($posko['latitude'] === null || $posko['longitude'] === null) {
            $this->command->error('KKN_POSKO_LATITUDE dan KKN_POSKO_LONGITUDE harus diisi di .env.');

            return;
        }

        $defaults = [
            'posko_latitude' => $posko['latitude'],
            'posko_longitude' => $posko['longitude'],
            'geofence_radius_m' => $posko['radius_m'],
            'max_gps_accuracy_m' => $posko['max_gps_accuracy_m'],
        ];

        foreach ($defaults as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => (string) $value]);
        }

        $this->command->info('Pengaturan posko siap.');
    }
}
