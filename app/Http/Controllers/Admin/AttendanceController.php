<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\ScheduleService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request, ScheduleService $schedule): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $today = CarbonImmutable::now(config('app.timezone'))->toDateString();
        $from = $filters['from'] ?? $today;
        $to = $filters['to'] ?? $today;

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $name = trim($filters['name'] ?? '');

        $query = Attendance::query()
            ->join('users', 'users.id', '=', 'attendances.user_id')
            ->select('attendances.*', 'users.name as member_name')
            ->whereDate('attendances.attendance_date', '>=', $from)
            ->whereDate('attendances.attendance_date', '<=', $to)
            ->orderByDesc('attendances.attendance_date')
            ->orderByDesc('attendances.attended_at');

        if ($name !== '') {
            $query->where('users.name', 'like', '%' . $name . '%');
        }

        $attendances = $query->paginate(20)->withQueryString();

        $attendances->getCollection()->each(function ($row) use ($schedule) {
            $row->category_label = $schedule->label($row->category_snapshot);
        });

        return view('admin.attendances.index', [
            'attendances' => $attendances,
            'from' => $from,
            'to' => $to,
            'name' => $name,
        ]);
    }
}
