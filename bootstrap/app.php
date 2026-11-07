<?php

use App\Http\Middleware\BlockWhenImpersonating;
use App\Http\Middleware\EnsureActive;
use App\Http\Middleware\EnsureRole;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => EnsureRole::class,
            'aktif' => EnsureActive::class,
            'bukan.impersonasi' => BlockWhenImpersonating::class,
        ]);

        $middleware->redirectGuestsTo(fn() => route('login'));

        $middleware->redirectUsersTo(
            fn(Request $request) => $request->user()?->role === 'admin' ? '/admin' : '/anggota'
        );

        $middleware->trustProxies(at: '127.0.0.1');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
