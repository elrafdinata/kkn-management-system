<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\GeofenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request, GeofenceService $geofence): RedirectResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $key = Str::lower($data['username']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($key);

            throw ValidationException::withMessages([
                'username' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        $credentials = [
            'username' => $data['username'],
            'password' => $data['password'],
            'is_active' => true,
        ];

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($key, 60);

            throw ValidationException::withMessages([
                'username' => 'Username atau password salah, atau akun tidak aktif.',
            ]);
        }

        RateLimiter::clear($key);

        // Admin bebas geofence. Anggota wajib berada dalam radius posko.
        if (Auth::user()->role !== 'admin') {
            $result = $geofence->check(
                $this->numberOrNull($request->input('latitude')),
                $this->numberOrNull($request->input('longitude')),
                $this->numberOrNull($request->input('accuracy')),
            );

            if (! $result['allowed']) {
                $detail = $result['distance_m'] !== null ? " (jarak {$result['distance_m']} m)" : '';

                ActivityLog::record(
                    $request,
                    'login_ditolak_lokasi',
                    "Login ditolak: {$result['reason']}{$detail}"
                );

                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'username' => $result['message'],
                ]);
            }
        }

        $request->session()->regenerate();

        return redirect()->intended(
            Auth::user()->role === 'admin' ? '/admin' : '/anggota'
        );
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function numberOrNull(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
