<?php

namespace App\Providers;

use App\Support\Madrasah;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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

        // Penghitung badge menu sidebar (surat masuk & tugas TU yang belum selesai), cache singkat.
        View::composer('components.sidebar', function ($view) {
            try {
                $badges = Cache::remember('nav.badges', 60, fn () => [
                    'surat_masuk' => DB::table('surat_masuk')->where('status', '!=', 'selesai')->count(),
                    'tasks' => DB::table('tasks')->where('status', '!=', 'selesai')->count(),
                ]);
            } catch (\Throwable $e) {
                $badges = ['surat_masuk' => 0, 'tasks' => 0];
            }
            $view->with('navBadges', $badges);
        });

        // Di produksi semua URL (aset, redirect, tautan) harus https.
        if ($this->app->isProduction()) {
            URL::forceScheme('https');
        }

        // Batas percobaan login: per email+IP (5/menit) supaya satu orang yang salah ketik tidak
        // mengunci seluruh madrasah yang berbagi satu IP, plus batas longgar per IP (30/menit).
        // Lupa kata sandi: 3 permintaan/menit per email+IP (mencegah membanjiri kotak surat orang lain).
        RateLimiter::for('lupa-sandi', fn (Request $request) => [
            Limit::perMinute(3)->by(Str::lower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(20)->by($request->ip()),
        ]);
        RateLimiter::for('reset-sandi', fn (Request $request) => Limit::perMinute(10)->by($request->ip()));

        // Email atur ulang kata sandi berbahasa Indonesia, memakai nama madrasah dari Pengaturan.
        ResetPassword::toMailUsing(function ($user, string $token) {
            $madrasah = app(Madrasah::class);
            $url = url(route('password.reset', ['token' => $token, 'email' => $user->getEmailForPasswordReset()], false));

            return (new MailMessage)
                ->subject('Atur ulang kata sandi - '.$madrasah->nama_pendek)
                ->greeting('Assalamu\'alaikum, '.$user->name)
                ->line('Kami menerima permintaan mengatur ulang kata sandi akun e-Madrasah Anda.')
                ->action('Atur Ulang Kata Sandi', $url)
                ->line('Tautan ini berlaku '.config('auth.passwords.'.config('auth.defaults.passwords').'.expire').' menit dan hanya bisa dipakai sekali.')
                ->line('Jika Anda tidak merasa memintanya, abaikan email ini; kata sandi Anda tidak berubah.')
                ->salutation('Terima kasih, '.$madrasah->nama);
        });

        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by(Str::lower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(30)->by($request->ip()),
        ]);
    }
}
