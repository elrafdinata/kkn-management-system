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
        if ($attendance && $attendance->attended_at) {
            $attendanceStatus = 'hadir';
        } elseif ($attendance) {
            $attendanceStatus = 'tidak_hadir';
        } elseif ($isRequiredToday) {
            $attendanceStatus = 'menunggu';
        } else {
            $attendanceStatus = 'tidak_wajib';
        }

        $attendedTime = null;
        if ($attendanceStatus === 'hadir') {
            $attendedTime = Carbon::parse($attendance->attended_at)
                ->timezone(config('app.timezone'))
                ->format('H:i');
        }

        return view('anggota.dashboard', [
            'categoryLabel' => $schedule->label($user->schedule_category),
            'isRequiredToday' => $isRequiredToday,
            'todayText' => $today->locale('id')->translatedFormat('l, d F Y'),
            'attendanceStatus' => $attendanceStatus,
            'attendedTime' => $attendedTime,
        ]);
    }
}
