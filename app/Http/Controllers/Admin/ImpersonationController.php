<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(Request $request, User $member): RedirectResponse
    {
        $admin = $request->user();

        if ($member->role !== 'anggota' || ! $member->is_active) {
            return back()->withErrors([
                'impersonasi' => 'Hanya anggota yang aktif yang bisa dilihat tampilannya.',
            ]);
        }

        // Dicatat sebelum berpindah akun, supaya tercatat atas nama admin.
        ActivityLog::record(
            $request,
            'impersonasi_mulai',
            "Admin {$admin->name} masuk sebagai anggota {$member->name} (mode lihat tampilan)."
        );

        $adminId = $admin->id;

        Auth::login($member);
        $request->session()->put('impersonator_id', $adminId);

        return redirect()->route('anggota.dashboard');
    }

    public function stop(Request $request): RedirectResponse
    {
        $adminId = $request->session()->get('impersonator_id');

        if (! $adminId) {
            return redirect()->route('login');
        }

        $member = $request->user();

        $admin = User::query()
            ->where('id', $adminId)
            ->where('role', 'admin')
            ->where('is_active', true)
            ->first();

        if (! $admin) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login');
        }

        Auth::login($admin);
        $request->session()->forget('impersonator_id');

        ActivityLog::record(
            $request,
            'impersonasi_selesai',
            "Admin {$admin->name} kembali dari mode lihat tampilan anggota {$member?->name}."
        );

        return redirect()->route('admin.dashboard');
    }
}
