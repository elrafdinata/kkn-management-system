<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Services\ScheduleService;
use Carbon\CarbonImmutable;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const WINDOW_DAYS = 30;

    public function index(ScheduleService $schedule): View
    {
        $tz = config('app.timezone');
        $todayDate = CarbonImmutable::now($tz)->startOfDay();
        $today = $todayDate->toDateString();
        $yesterday = $todayDate->subDay()->toDateString();
        $windowStart = $todayDate->subDays(self::WINDOW_DAYS)->toDateString();
        $chartStart = $todayDate->subDays(6);

        $members = User::query()
            ->where('role', 'anggota')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'schedule_category', 'created_at']);

        $rows = Attendance::query()
            ->whereDate('attendance_date', '>=', $windowStart)
            ->whereDate('attendance_date', '<=', $today)
            ->get(['user_id', 'attendance_date', 'status']);

        // $statusMap[user_id][Y-m-d] = status
        $statusMap = [];
        foreach ($rows as $r) {
            $statusMap[$r->user_id][$r->attendance_date->toDateString()] = $r->status;
        }

        $statusOf = fn($userId, $date) => $statusMap[$userId][$date] ?? null;

        // ---------- Ringkasan hari ini (hanya anggota yang wajib hari ini) ----------
        $required = $members->filter(
            fn($m) => $schedule->isRequiredDay($m->schedule_category, $today)
        );

        $hadir = $required->filter(fn($m) => $statusOf($m->id, $today) === Attendance::STATUS_HADIR)->count();
        $izin = $required->filter(fn($m) => $statusOf($m->id, $today) === Attendance::STATUS_IZIN)->count();
        $libur = $required->filter(fn($m) => $statusOf($m->id, $today) === Attendance::STATUS_LIBUR)->count();
        $tidakHadir = $required->filter(fn($m) => $statusOf($m->id, $today) === Attendance::STATUS_TIDAK_HADIR)->count();

        $waiting = $required
            ->filter(fn($m) => $statusOf($m->id, $today) === null)
            ->map(fn($m) => [
                'name' => $m->name,
                'category' => $schedule->label($m->schedule_category),
            ])
            ->values();

        // ---------- Statistik per anggota: 30 hari terakhir sampai kemarin ----------
        $memberStats = $members->map(function ($m) use ($schedule, $statusOf, $windowStart, $yesterday, $tz) {
            $createdDate = $m->created_at
                ? $m->created_at->copy()->setTimezone($tz)->toDateString()
                : $windowStart;
            $start = max($windowStart, $createdDate);

            $days = $yesterday >= $start
                ? $schedule->requiredDays($m->schedule_category, $start, $yesterday)
                : [];

            $h = 0;
            $i = 0;
            $l = 0;
            foreach ($days as $d) {
                $s = $statusOf($m->id, $d);
                if ($s === Attendance::STATUS_HADIR) {
                    $h++;
                } elseif ($s === Attendance::STATUS_IZIN) {
                    $i++;
                } elseif ($s === Attendance::STATUS_LIBUR) {
                    $l++;
                }
            }

            return [
                'name' => $m->name,
                'category' => $m->schedule_category,
                'required' => count($days) - $l,
                'hadir' => $h,
                'izin' => $i,
                'missed' => max(0, count($days) - $h - $i - $l),
            ];
        });

        $percent = function ($hadir, $required, $izin) {
            $denominator = $required - $izin;

            return $denominator > 0 ? (int) round($hadir / $denominator * 100) : null;
        };

        // ---------- Persentase per kategori ----------
        $categoryStats = [];
        foreach (ScheduleService::CATEGORIES as $key => $label) {
            $group = $memberStats->where('category', $key);

            $categoryStats[] = [
                'label' => $label,
                'members' => $group->count(),
                'hadir' => $group->sum('hadir'),
                'izin' => $group->sum('izin'),
                'missed' => $group->sum('missed'),
                'percent' => $percent($group->sum('hadir'), $group->sum('required'), $group->sum('izin')),
            ];
        }

        $overallPercent = $percent(
            $memberStats->sum('hadir'),
            $memberStats->sum('required'),
            $memberStats->sum('izin')
        );

        // ---------- Anggota dengan Tidak Hadir terbanyak ----------
        $topMissed = $memberStats
            ->filter(fn($s) => $s['missed'] > 0)
            ->sortByDesc('missed')
            ->take(5)
            ->values();

        // ---------- Tren 7 hari terakhir (termasuk hari ini) ----------
        $trend = [];
        for ($d = $chartStart; $d->lte($todayDate); $d = $d->addDay()) {
            $date = $d->toDateString();
            $isToday = $date === $today;

            $dayRequired = $members->filter(function ($m) use ($schedule, $date, $tz) {
                $created = $m->created_at
                    ? $m->created_at->copy()->setTimezone($tz)->toDateString()
                    : '0000-00-00';

                return $created <= $date && $schedule->isRequiredDay($m->schedule_category, $date);
            });

            $h = $dayRequired->filter(fn($m) => $statusOf($m->id, $date) === Attendance::STATUS_HADIR)->count();
            $i = $dayRequired->filter(fn($m) => $statusOf($m->id, $date) === Attendance::STATUS_IZIN)->count();
            $l = $dayRequired->filter(fn($m) => $statusOf($m->id, $date) === Attendance::STATUS_LIBUR)->count();
            $total = $dayRequired->count() - $l;

            $trend[] = [
                'label' => $d->locale('id')->translatedFormat('D d/m'),
                'required' => $total,
                'hadir' => $h,
                'izin' => $i,
                'missed' => $isToday ? 0 : max(0, $total - $h - $i),
                'waiting' => $isToday ? max(0, $total - $h - $i) : 0,
            ];
        }

        return view('admin.dashboard', [
            'today' => $today,
            'totalMembers' => $members->count(),
            'requiredCount' => $required->count() - $libur,
            'libur' => $libur,
            'hadir' => $hadir,
            'izin' => $izin,
            'tidakHadir' => $tidakHadir,
            'waiting' => $waiting,
            'windowDays' => self::WINDOW_DAYS,
            'overallPercent' => $overallPercent,
            'categoryStats' => $categoryStats,
            'topMissed' => $topMissed,
            'trend' => $trend,
        ]);
    }
}
