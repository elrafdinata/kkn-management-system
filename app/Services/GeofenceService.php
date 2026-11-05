<?php

namespace App\Services;

use App\Models\Setting;

class GeofenceService
{
    private const EARTH_RADIUS_M = 6371000;

    /**
     * Periksa apakah koordinat berada dalam radius posko.
     *
     * @return array{allowed: bool, reason: string, message: string, distance_m: ?float}
     */
    public function check(?float $latitude, ?float $longitude, ?float $accuracy): array
    {
        if ($latitude === null || $longitude === null || $accuracy === null) {
            return $this->deny('no_gps', 'Lokasi tidak tersedia. Aktifkan GPS dan izinkan akses lokasi.');
        }

        if ($latitude < -90 || $latitude > 90 || $longitude < -180 || $longitude > 180) {
            return $this->deny('invalid_gps', 'Data lokasi tidak valid.');
        }

        $poskoLat = Setting::getValue('posko_latitude');
        $poskoLng = Setting::getValue('posko_longitude');

        if ($poskoLat === null || $poskoLng === null) {
            return $this->deny('not_configured', 'Koordinat posko belum diatur. Hubungi administrator.');
        }

        $radius = (float) Setting::getValue('geofence_radius_m', '30');
        $maxAccuracy = (float) Setting::getValue('max_gps_accuracy_m', '50');

        if ($accuracy > $maxAccuracy) {
            return $this->deny('poor_accuracy', 'Akurasi GPS kurang baik. Coba lagi di tempat terbuka.');
        }

        $distance = $this->haversine($latitude, $longitude, (float) $poskoLat, (float) $poskoLng);

        if ($distance > $radius) {
            return $this->deny('outside_radius', 'Anda berada di luar area posko.', $distance);
        }

        return [
            'allowed' => true,
            'reason' => 'ok',
            'message' => 'Lokasi sesuai.',
            'distance_m' => round($distance, 1),
        ];
    }

    public function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return self::EARTH_RADIUS_M * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function deny(string $reason, string $message, ?float $distance = null): array
    {
        return [
            'allowed' => false,
            'reason' => $reason,
            'message' => $message,
            'distance_m' => $distance !== null ? round($distance, 1) : null,
        ];
    }
}
