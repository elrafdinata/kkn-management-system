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
    public function index(ScheduleService $schedule): View
    {
        $today = CarbonImmutable::now(config('app.timezone'))->toDateString();

        $members = User::query()
            ->where('role', 'anggota')
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'schedule_category']);

        $statusByUser = Attendance::query()
            ->whereDate('attendance_date', $today)
            ->pluck('status', 'user_id');

        $required = $members->filter(
            fn($m) => $schedule->isRequiredDay($m->schedule_category, $today)
        );

        $hadir = $members->filter(fn($m) => $statusByUser->get($m->id) === 'hadir')->count();
        $tidakHadir = $members->filter(fn($m) => $statusByUser->get($m->id) === 'tidak_hadir')->count();

        $waiting = $required
            ->filter(fn($m) => ! $statusByUser->has($m->id))
            ->map(fn($m) => [
                'name' => $m->name,
                'category' => $schedule->label($m->schedule_category),
            ])
            ->values();

        return view('admin.dashboard', [
            'today' => $today,
            'totalMembers' => $members->count(),
            'requiredCount' => $required->count(),
            'hadir' => $hadir,
            'tidakHadir' => $tidakHadir,
            'waiting' => $waiting,
        ]);
    }
}
