<?php

namespace App\Http\Controllers\Anggota;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Services\GeofenceService;
use App\Services\ScheduleService;
use Carbon\CarbonImmutable;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class AbsenController extends Controller
{
    public function __construct(
        private ScheduleService $schedule,
        private GeofenceService $geofence,
    ) {}

    public function show(Request $request): View
    {
        $problem = $this->precheck($request);

        if ($problem) {
            return $this->result($problem['type'], $problem['message']);
        }

        return $this->result(
            'info',
            'Buka kamera, ambil selfie, lalu kirim absen. Lokasi Anda akan diperiksa saat dikirim.',
            true
        );
    }

    public function lokasi(Request $request): View
    {
        $problem = $this->precheck($request);

        if ($problem) {
            return $this->result($problem['type'], $problem['message']);
        }

        $validator = Validator::make($request->all(), [
            'selfie' => ['required', 'file', 'image', 'mimetypes:image/jpeg', 'max:5120'],
        ], [
            'selfie.required' => 'Selfie wajib diambil dari kamera.',
            'selfie.uploaded' => 'Selfie gagal diunggah. Ambil ulang fotonya.',
            'selfie.file' => 'Selfie tidak valid. Ambil ulang fotonya.',
            'selfie.image' => 'Selfie harus berupa foto JPEG.',
            'selfie.mimetypes' => 'Selfie harus berformat JPEG.',
            'selfie.max' => 'Ukuran selfie maksimal 5 MB.',
        ]);

        if ($validator->fails()) {
            return $this->result('danger', $validator->errors()->first('selfie'), true);
        }

        $latitude = $this->numberOrNull($request->input('latitude'));
        $longitude = $this->numberOrNull($request->input('longitude'));
        $accuracy = $this->numberOrNull($request->input('accuracy'));

        $result = $this->geofence->check($latitude, $longitude, $accuracy);

        if (! $result['allowed']) {
            $detail = $result['distance_m'] !== null ? " (jarak {$result['distance_m']} m)" : '';

            ActivityLog::record(
                $request,
                'absen_ditolak_lokasi',
                "Absen ditolak: {$result['reason']}{$detail}"
            );

            return $this->result('danger', $result['message'], true);
        }

        $user = $request->user();
        $today = CarbonImmutable::now(config('app.timezone'))->toDateString();
        $path = null;

        try {
            $path = Storage::disk('selfie')->putFile($today, $request->file('selfie'));

            DB::transaction(function () use ($user, $today, $latitude, $longitude, $accuracy, $result, $path) {
                Attendance::create([
                    'user_id' => $user->id,
                    'attendance_date' => $today,
                    'status' => Attendance::STATUS_HADIR,
                    'attended_at' => now(),
                    'category_snapshot' => $user->schedule_category,
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'accuracy_m' => $accuracy,
                    'distance_m' => $result['distance_m'],
                    'selfie_path' => $path,
                    'qr_session_id' => null,
                ]);
            });
        } catch (UniqueConstraintViolationException) {
            $this->discardSelfie($path);

            return $this->result('info', 'Anda sudah tercatat absen hari ini.');
        } catch (Throwable $e) {
            $this->discardSelfie($path);

            throw $e;
        }

        ActivityLog::record(
            $request,
            'absen_berhasil',
            "Absen berhasil tanggal {$today} (jarak {$result['distance_m']} m)"
        );

        return $this->result('success', 'Absensi berhasil dicatat. Terima kasih.');
    }

    /**
     * @return array{type: string, message: string}|null  null berarti semua pemeriksaan lolos
     */
    private function precheck(Request $request): ?array
    {
        $user = $request->user();

        if (! $this->schedule->isRequiredToday($user->schedule_category)) {
            return ['type' => 'secondary', 'message' => 'Hari ini bukan hari wajib Anda, jadi tidak perlu absen.'];
        }

        $today = CarbonImmutable::now(config('app.timezone'))->toDateString();

        $already = Attendance::where('user_id', $user->id)
            ->where('attendance_date', $today)
            ->exists();

        if ($already) {
            return ['type' => 'info', 'message' => 'Anda sudah tercatat absen hari ini.'];
        }

        return null;
    }

    private function discardSelfie(?string $path): void
    {
        if ($path !== null && $path !== '') {
            Storage::disk('selfie')->delete($path);
        }
    }

    private function numberOrNull(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function result(string $type, string $message, bool $showForm = false): View
    {
        return view('anggota.absen', ['type' => $type, 'message' => $message, 'showForm' => $showForm]);
    }
}
