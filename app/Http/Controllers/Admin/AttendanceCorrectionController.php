<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\User;
use App\Services\ScheduleService;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceCorrectionController extends Controller
{
    public function create(Request $request): View
    {
        $today = CarbonImmutable::now(config('app.timezone'))->toDateString();

        $members = User::query()
            ->where('role', 'anggota')
            ->orderBy('name')
            ->get(['id', 'name', 'schedule_category']);

        return view('admin.attendances.correct', [
            'members' => $members,
            'memberId' => (int) $request->query('member', 0),
            'date' => (string) $request->query('date', $today),
            'today' => $today,
        ]);
    }

    public function store(Request $request, ScheduleService $schedule): RedirectResponse
    {
        $today = CarbonImmutable::now(config('app.timezone'))->toDateString();

        $data = $request->validate([
            'member_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'anggota')],
            'date' => ['required', 'date_format:Y-m-d'],
            'status' => ['required', Rule::in([
                Attendance::STATUS_HADIR,
                Attendance::STATUS_TIDAK_HADIR,
                Attendance::STATUS_IZIN,
                Attendance::STATUS_LIBUR,
            ])],
            'reason' => ['required', 'string', 'min:5', 'max:255'],
        ], [
            'member_id.required' => 'Pilih anggota.',
            'member_id.exists' => 'Anggota tidak ditemukan.',
            'date.required' => 'Tanggal wajib diisi.',
            'date.date_format' => 'Format tanggal tidak valid.',
            'status.required' => 'Pilih status.',
            'status.in' => 'Status tidak valid.',
            'reason.required' => 'Alasan wajib diisi.',
            'reason.min' => 'Alasan minimal 5 karakter.',
            'reason.max' => 'Alasan maksimal 255 karakter.',
        ]);

        $date = $data['date'];
        $newStatus = $data['status'];
        $reason = trim($data['reason']);

        $futureAllowed = [Attendance::STATUS_IZIN, Attendance::STATUS_LIBUR];

        // Hadir dan Tidak Hadir tidak boleh untuk tanggal ke depan. Izin dan Libur boleh.
        if (! in_array($newStatus, $futureAllowed, true) && $date > $today) {
            return back()->withInput()->withErrors([
                'date' => 'Tanggal tidak boleh melewati hari ini.',
            ]);
        }

        if ($newStatus === Attendance::STATUS_TIDAK_HADIR && $date >= $today) {
            return back()->withInput()->withErrors([
                'date' => 'Tidak Hadir hanya bisa ditetapkan untuk hari yang sudah berakhir.',
            ]);
        }

        $member = User::findOrFail($data['member_id']);

        $labels = [
            Attendance::STATUS_HADIR => 'Hadir',
            Attendance::STATUS_TIDAK_HADIR => 'Tidak Hadir',
            Attendance::STATUS_IZIN => 'Izin',
            Attendance::STATUS_LIBUR => 'Libur',
        ];

        try {
            DB::transaction(function () use ($request, $schedule, $member, $date, $newStatus, $reason, $labels, $futureAllowed) {
                $row = Attendance::query()
                    ->where('user_id', $member->id)
                    ->whereDate('attendance_date', $date)
                    ->lockForUpdate()
                    ->first();

                $category = $row?->category_snapshot ?? $member->schedule_category;

                if ($category === null) {
                    throw new DomainException('Anggota ini belum punya kategori jadwal.');
                }

                if ($newStatus !== Attendance::STATUS_HADIR && ! $schedule->isRequiredDay($category, $date)) {
                    throw new DomainException('Tanggal itu bukan hari wajib bagi anggota ini, jadi tidak bisa ditetapkan ' . $labels[$newStatus] . '.');
                }

                if ($row) {
                    if ($row->status === $newStatus && ! in_array($newStatus, $futureAllowed, true)) {
                        throw new DomainException('Status anggota pada tanggal itu sudah ' . $labels[$newStatus] . '. Tidak ada yang diubah.');
                    }

                    $oldLabel = $labels[$row->status] ?? $row->status;
                    $row->status = $newStatus;
                    $row->reason = $reason;
                    $row->save();
                } else {
                    $oldLabel = 'belum ada data';

                    Attendance::create([
                        'user_id' => $member->id,
                        'attendance_date' => $date,
                        'status' => $newStatus,
                        'reason' => $reason,
                        'attended_at' => null,
                        'category_snapshot' => $category,
                    ]);
                }

                ActivityLog::record(
                    $request,
                    'koreksi_absensi',
                    sprintf(
                        'Koreksi absensi %s tanggal %s: %s menjadi %s. Alasan: %s',
                        $member->name,
                        $date,
                        $oldLabel,
                        $labels[$newStatus],
                        $reason
                    )
                );
            });
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['member_id' => $e->getMessage()]);
        } catch (UniqueConstraintViolationException) {
            return back()->withInput()->withErrors([
                'member_id' => 'Data absensi anggota ini baru saja dibuat. Muat ulang halaman lalu coba lagi.',
            ]);
        }

        return redirect()
            ->route('admin.attendances.index', ['from' => $date, 'to' => $date])
            ->with('success', 'Koreksi absensi disimpan.');
    }
}
