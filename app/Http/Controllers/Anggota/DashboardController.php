<?php

namespace App\Http\Controllers\Anggota;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\ScheduleService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, ScheduleService $schedule): View
    {
        $user = $request->user();
        $today = CarbonImmutable::now(config('app.timezone'));
        $isRequiredToday = $schedule->isRequiredToday($user->schedule_category);

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('attendance_date', $today->toDateString())
            ->first();

        // Menunggu tidak disimpan: dihitung dari hari wajib dan ketiadaan baris.
        if ($attendance && $attendance->status === 'hadir') {
            $attendanceStatus = 'hadir';
        } elseif ($attendance) {
            $attendanceStatus = 'tidak_hadir';
        } elseif ($isRequiredToday) {
            $attendanceStatus = 'menunggu';
        } else {
            $attendanceStatus = 'tidak_wajib';
        }

        $attendedTime = null;
        if ($attendanceStatus === 'hadir' && $attendance->attended_at) {
            $attendedTime = Carbon::parse($attendance->attended_at)
                ->timezone(config('app.timezone'))
                ->format('H:i');
        }

        $hadirCount = Attendance::where('user_id', $user->id)->where('status', 'hadir')->count();
        $tidakHadirCount = Attendance::where('user_id', $user->id)->where('status', 'tidak_hadir')->count();
        $total = $hadirCount + $tidakHadirCount;
        $percent = $total > 0 ? (int) round($hadirCount / $total * 100) : null;

        $history = Attendance::where('user_id', $user->id)
            ->orderByDesc('attendance_date')
            ->limit(10)
            ->get(['id', 'attendance_date', 'status', 'attended_at']);

        return view('anggota.dashboard', [
            'categoryLabel' => $schedule->label($user->schedule_category),
            'isRequiredToday' => $isRequiredToday,
            'todayText' => $today->locale('id')->translatedFormat('l, d F Y'),
            'attendanceStatus' => $attendanceStatus,
            'attendedTime' => $attendedTime,
            'hadirCount' => $hadirCount,
            'tidakHadirCount' => $tidakHadirCount,
            'percent' => $percent,
            'history' => $history,
        ]);
    }
}
