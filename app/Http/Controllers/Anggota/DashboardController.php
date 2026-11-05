<?php

namespace App\Http\Controllers\Anggota;

use App\Http\Controllers\Controller;
use App\Services\ScheduleService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request, ScheduleService $schedule): View
    {
        $user = $request->user();
        $today = CarbonImmutable::now(config('app.timezone'));

        return view('anggota.dashboard', [
            'categoryLabel' => $schedule->label($user->schedule_category),
            'isRequiredToday' => $schedule->isRequiredToday($user->schedule_category),
            'todayText' => $today->locale('id')->translatedFormat('l, d F Y'),
        ]);
    }
}
