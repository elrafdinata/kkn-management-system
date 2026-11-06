<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Filesystem\FilesystemAdapter;

class SelfieController extends Controller
{
    public function show(Request $request, Attendance $attendance): StreamedResponse
    {
        $user = $request->user();

        $allowed = $user->role === 'admin' || $attendance->user_id === $user->id;
        $path = $attendance->selfie_path;
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('selfie');

        abort_unless($allowed && $path && $disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
