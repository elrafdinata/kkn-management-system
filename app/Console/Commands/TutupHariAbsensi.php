<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use App\Models\User;
use App\Services\ScheduleService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Database\UniqueConstraintViolationException;
use Throwable;

class TutupHariAbsensi extends Command
{
    protected $signature = 'absensi:tutup-hari
                            {tanggal? : Tanggal Y-m-d yang ditutup. Bawaan: kemarin}
                            {--dry-run : Hanya tampilkan hasil, tidak menulis ke database}';

    protected $description = 'Tetapkan status Tidak Hadir untuk anggota yang wajib hadir tetapi tidak absen pada hari yang sudah berakhir';

    public function handle(ScheduleService $schedule): int
    {
        $tz = config('app.timezone');
        $today = CarbonImmutable::now($tz)->startOfDay();
        $input = $this->argument('tanggal');

        try {
            $date = $input !== null
                ? CarbonImmutable::parse($input, $tz)->startOfDay()
                : $today->subDay();
        } catch (Throwable) {
            $this->error('Format tanggal tidak valid. Gunakan Y-m-d, contoh 2026-11-05.');

            return self::FAILURE;
        }

        if ($date->gte($today)) {
            $this->error('Hari ' . $date->toDateString() . ' belum berakhir. Hanya hari yang sudah lewat yang bisa ditutup.');

            return self::FAILURE;
        }

        $dateText = $date->toDateString();
        $dryRun = (bool) $this->option('dry-run');

        $members = User::query()
            ->where('role', 'anggota')
            ->where('is_active', true)
            ->whereNotNull('schedule_category')
            ->whereDate('created_at', '<=', $dateText)
            ->get();

        $alreadyIds = Attendance::whereDate('attendance_date', $dateText)
            ->pluck('user_id')
            ->all();

        $created = 0;
        $skippedNotRequired = 0;
        $skippedHasRow = 0;

        foreach ($members as $member) {
            if (! $schedule->isRequiredDay($member->schedule_category, $date)) {
                $skippedNotRequired++;
                continue;
            }

            if (in_array($member->id, $alreadyIds, true)) {
                $skippedHasRow++;
                continue;
            }

            if ($dryRun) {
                $this->line('[dry-run] Tidak Hadir: ' . $member->name);
                $created++;
                continue;
            }

            try {
                Attendance::create([
                    'user_id' => $member->id,
                    'attendance_date' => $dateText,
                    'status' => Attendance::STATUS_TIDAK_HADIR,
                    'attended_at' => null,
                    'category_snapshot' => $member->schedule_category,
                ]);
                $created++;
            } catch (UniqueConstraintViolationException) {
                $skippedHasRow++;
            }
        }

        $this->info(($dryRun ? '[dry-run] ' : '') . "Tanggal {$dateText}: {$members->count()} anggota diperiksa.");
        $this->line("Tidak Hadir " . ($dryRun ? 'akan ditetapkan' : 'ditetapkan') . ": {$created}");
        $this->line("Dilewati, bukan hari wajib: {$skippedNotRequired}");
        $this->line("Dilewati, sudah punya data absensi: {$skippedHasRow}");

        return self::SUCCESS;
    }
}
