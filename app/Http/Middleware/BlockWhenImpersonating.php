<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockWhenImpersonating
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('impersonator_id')) {
            return back()->withErrors([
                'impersonasi' => 'Tidak bisa dilakukan saat mode lihat tampilan. Klik "Kembali ke admin" terlebih dahulu.',
            ]);
        }

        return $next($request);
    }
}
