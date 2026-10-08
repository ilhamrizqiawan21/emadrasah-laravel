<?php

use App\Http\Middleware\EnsureUserHasRole;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Proxy tepercaya (TRUSTED_PROXIES) diatur lewat config/trustedproxy.php, bukan di sini:
        // env() di berkas ini dievaluasi sebelum .env dimuat sehingga nilainya tidak terbaca.
        // Periksa role SEBELUM route model binding. Kalau tidak, pengguna tanpa hak akses mendapat 404
        // untuk id yang tidak ada tetapi 403 untuk id yang ada, sehingga bisa menebak id yang tersimpan.
        $middleware->prependToPriorityList(before: SubstituteBindings::class, prepend: EnsureUserHasRole::class);

        $middleware->append(SecurityHeaders::class);
        $middleware->alias([
            'role' => EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
