<?php

namespace App\Providers;

use App\Support\Madrasah;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Madrasah::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive(); // ← ini yang paling krusial

        // Identitas madrasah (nama, logo, warna…) tersedia di semua view sebagai $madrasah.
        View::share('madrasah', $this->app->make(Madrasah::class));

        // Di produksi semua URL (aset, redirect, tautan) harus https.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Batas percobaan login: per email+IP (5/menit) supaya satu orang yang salah ketik tidak
        // mengunci seluruh madrasah yang berbagi satu IP, plus batas longgar per IP (30/menit).
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by(Str::lower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(30)->by($request->ip()),
        ]);
    }
}
