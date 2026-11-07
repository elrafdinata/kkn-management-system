<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Carbon\CarbonImmutable;

class ReportService
{
    public function __construct(private ScheduleService $schedule) {}

    /**
     * @return array{rows: array<int, array<string, mixed>>, totals: array<string, mixed>}
     */
    public function build(string $from, string $to, ?string $category = null, string $name = '', ?int $userId = null): array
    {
        $tz = config('app.timezone');
        $today = CarbonImmutable::now($tz)->toDateString();

        $members = User::query()
            ->where('role', 'anggota')
            ->where('is_active', true)
            ->when($userId, fn($q) => $q->where('id', $userId))
            ->when($category, fn($q) => $q->where('schedule_category', $category))
            ->when($name !== '', fn($q) => $q->where('name', 'like', '%' . $name . '%'))
            ->orderBy('name')
            ->get(['id', 'name', 'schedule_category', 'created_at']);

        $statusMap = [];
        if ($members->isNotEmpty()) {
            $records = Attendance::query()
                ->whereIn('user_id', $members->pluck('id'))
                ->whereDate('attendance_date', '>=', $from)
                ->whereDate('attendance_date', '<=', $to)
                ->get(['user_id', 'attendance_date', 'status']);

            foreach ($records as $r) {
                $statusMap[$r->user_id][$r->attendance_date->toDateString()] = $r->status;
            }
        }

        $rows = [];
        $totals = ['required' => 0, 'hadir' => 0, 'izin' => 0, 'libur' => 0, 'tidak_hadir' => 0, 'menunggu' => 0];

        foreach ($members as $m) {
            $created = $m->created_at
                ? $m->created_at->copy()->setTimezone($tz)->toDateString()
                : $from;

            $start = max($from, $created);
            $end = min($to, $today);

            $days = $start <= $end
                ? $this->schedule->requiredDays($m->schedule_category, $start, $end)
                : [];

            $h = 0;
            $i = 0;
            $l = 0;
            $x = 0;
            $w = 0;

            foreach ($days as $d) {
                $s = $statusMap[$m->id][$d] ?? null;

                if ($s === Attendance::STATUS_HADIR) {
                    $h++;
                } elseif ($s === Attendance::STATUS_IZIN) {
                    $i++;
                } elseif ($s === Attendance::STATUS_LIBUR) {
                    $l++;
                } elseif ($s === Attendance::STATUS_TIDAK_HADIR) {
                    $x++;
                } elseif ($d < $today) {
                    $x++;
                } else {
                    $w++;
                }
            }

            // Hari Libur dikeluarkan dari hari wajib.
            $required = count($days) - $l;

            $rows[] = [
                'name' => $m->name,
                'category' => $this->schedule->label($m->schedule_category),
                'required' => $required,
                'hadir' => $h,
                'izin' => $i,
                'libur' => $l,
                'tidak_hadir' => $x,
                'menunggu' => $w,
                'percent' => $this->percent($h, $x),
            ];

            $totals['required'] += $required;
            $totals['hadir'] += $h;
            $totals['izin'] += $i;
            $totals['libur'] += $l;
            $totals['tidak_hadir'] += $x;
            $totals['menunggu'] += $w;
        }

        $totals['percent'] = $this->percent($totals['hadir'], $totals['tidak_hadir']);

        return ['rows' => $rows, 'totals' => $totals];
    }

    private function percent(int $hadir, int $tidakHadir): ?int
    {
        $denominator = $hadir + $tidakHadir;

        return $denominator > 0 ? (int) round($hadir / $denominator * 100) : null;
    }
}
