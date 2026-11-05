<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\QrSession;
use App\Services\QrSessionService;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QrController extends Controller
{
    public function show(): View
    {
        $today = CarbonImmutable::now(config('app.timezone'))->toDateString();
        $session = QrSession::where('attendance_date', $today)->first();

        $svg = null;
        $token = session('qr_token');

        if ($token) {
            $url = url('/anggota/absen') . '?t=' . $token;
            $writer = new Writer(new ImageRenderer(
                new RendererStyle(280),
                new SvgImageBackEnd()
            ));
            $svg = $writer->writeString($url);
        }

        return view('admin.qr.show', [
            'session' => $session,
            'svg' => $svg,
            'todayText' => CarbonImmutable::now(config('app.timezone'))
                ->locale('id')->translatedFormat('l, d F Y'),
        ]);
    }

    public function store(Request $request, QrSessionService $service): RedirectResponse
    {
        $result = $service->issueForToday($request->user()->id);

        ActivityLog::record(
            $request,
            $result['regenerated'] ? 'buat_ulang_qr' : 'buat_qr',
            ($result['regenerated'] ? 'Membuat ulang' : 'Membuat') . ' QR absensi tanggal ' . $result['session']->attendance_date->toDateString()
        );

        return redirect()->route('admin.qr.show')
            ->with('qr_token', $result['token'])
            ->with('success', 'QR berhasil dibuat. QR hanya tampil sekali di halaman ini.');
    }

    public function revoke(Request $request, QrSessionService $service): RedirectResponse
    {
        if ($service->revokeToday()) {
            ActivityLog::record($request, 'cabut_qr', 'Mencabut QR absensi hari ini');

            return redirect()->route('admin.qr.show')->with('success', 'QR hari ini dicabut.');
        }

        return redirect()->route('admin.qr.show')->with('success', 'Tidak ada QR aktif untuk dicabut.');
    }
}
