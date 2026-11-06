<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SelfieController extends Controller
{
    public function show(Request $request, Attendance $attendance): StreamedResponse
    {
        $user = $request->user();

        $allowed = $user->role === 'admin' || $attendance->user_id === $user->id;
        $path = $attendance->selfie_path;

        abort_unless($allowed && $path && Storage::disk('selfie')->exists($path), 404);

        return Storage::disk('selfie')->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
