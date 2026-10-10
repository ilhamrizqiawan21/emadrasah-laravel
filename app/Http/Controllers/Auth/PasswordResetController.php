<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\KataSandiKuat;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /** Jawaban yang sama untuk email terdaftar, tak terdaftar, atau nonaktif: tidak membocorkan akun mana yang ada. */
    private const PESAN_UMUM = 'Jika email terdaftar dan akunnya aktif, tautan pengaturan ulang kata sandi sudah dikirim.';

    public function form()
    {
        return view('auth.lupa-sandi');
    }

    public function kirim(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            Password::sendResetLink(['email' => $request->email, 'is_active' => true]);
        } catch (\Throwable $e) {
            // Mis. server email belum diatur/gagal: catat untuk admin, tetapi jangan beda jawaban ke pengunjung.
            report($e);
        }

        return back()->with('status', self::PESAN_UMUM);
    }

    public function formReset(Request $request, string $token)
    {
        return view('auth.reset-sandi', ['token' => $token, 'email' => $request->query('email')]);
    }

    public function simpan(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => ['required', 'string', 'max:255', 'confirmed', new KataSandiKuat($request->email)],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token') + ['is_active' => true],
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Tautan tidak valid atau sudah kedaluwarsa. Minta tautan baru.']);
        }

        return redirect()->route('login')->with('status', 'Kata sandi berhasil diubah. Silakan masuk dengan kata sandi baru.');
    }
}
