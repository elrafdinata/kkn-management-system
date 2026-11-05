<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class MemberController extends Controller
{
    private const CATEGORIES = ['stay', 'shift_malam', 'weekend'];

    public function index()
    {
        $members = User::where('role', 'anggota')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.members.index', compact('members'));
    }

    public function create()
    {
        return view('admin.members.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => [
                'required',
                'string',
                'min:3',
                'max:30',
                'regex:/^[a-z0-9._]+$/',
                Rule::unique('users', 'username'),
            ],
            'schedule_category' => ['required', Rule::in(self::CATEGORIES)],
            'password' => ['required', 'string', Password::min(8)],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.regex' => 'Username hanya boleh huruf kecil, angka, titik, dan garis bawah.',
            'username.unique' => 'Username sudah dipakai.',
            'schedule_category.required' => 'Kategori jadwal wajib dipilih.',
            'schedule_category.in' => 'Kategori jadwal tidak valid.',
            'password.required' => 'Password awal wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'password' => $data['password'],
            'role' => 'anggota',
            'schedule_category' => $data['schedule_category'],
            'is_active' => true,
        ]);

        ActivityLog::record(
            $request,
            'buat_akun',
            "Membuat akun anggota {$user->username} (kategori {$user->schedule_category})"
        );

        return redirect()
            ->route('admin.members.index')
            ->with('success', "Akun {$user->username} berhasil dibuat.");
    }

    public function edit(User $member)
    {
        $this->ensureMember($member);

        return view('admin.members.edit', compact('member'));
    }

    public function update(Request $request, User $member): RedirectResponse
    {
        $this->ensureMember($member);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'schedule_category' => ['required', Rule::in(self::CATEGORIES)],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'schedule_category.required' => 'Kategori jadwal wajib dipilih.',
            'schedule_category.in' => 'Kategori jadwal tidak valid.',
        ]);

        $oldCategory = $member->schedule_category;
        $member->update($data);

        ActivityLog::record(
            $request,
            'ubah_akun',
            "Mengubah akun {$member->username} (kategori {$oldCategory} menjadi {$member->schedule_category})"
        );

        return redirect()
            ->route('admin.members.edit', $member)
            ->with('success', 'Data anggota berhasil diperbarui.');
    }

    public function toggleStatus(Request $request, User $member): RedirectResponse
    {
        $this->ensureMember($member);

        $member->update(['is_active' => ! $member->is_active]);

        ActivityLog::record(
            $request,
            $member->is_active ? 'aktifkan_akun' : 'nonaktifkan_akun',
            ($member->is_active ? 'Mengaktifkan' : 'Menonaktifkan') . " akun {$member->username}"
        );

        return redirect()
            ->route('admin.members.edit', $member)
            ->with('success', $member->is_active ? 'Akun diaktifkan.' : 'Akun dinonaktifkan.');
    }

    public function resetPassword(Request $request, User $member): RedirectResponse
    {
        $this->ensureMember($member);

        $data = $request->validate([
            'password' => ['required', 'string', Password::min(8)],
        ], [
            'password.required' => 'Password baru wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        $member->update(['password' => $data['password']]);

        // Keluarkan anggota dari semua perangkat yang masih login.
        DB::table('sessions')->where('user_id', $member->id)->delete();

        ActivityLog::record($request, 'reset_password', "Mereset password akun {$member->username}");

        return redirect()
            ->route('admin.members.edit', $member)
            ->with('success', 'Password berhasil direset.');
    }

    /**
     * Halaman ini hanya untuk akun anggota, bukan administrator.
     */
    private function ensureMember(User $member): void
    {
        abort_unless($member->role === 'anggota', 404);
    }
}
