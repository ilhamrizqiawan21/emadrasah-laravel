<?php

namespace App\Http\Controllers;

use App\Rules\KataSandiKuat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AkunController extends Controller
{
    public function sandi()
    {
        return view('akun.sandi');
    }

    public function ubahSandi(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'current_password' => 'required|current_password',
            'password' => ['required', 'string', 'max:255', 'confirmed', 'different:current_password', new KataSandiKuat($user->email)],
        ], [
            'current_password.current_password' => 'Kata sandi saat ini salah.',
            'password.different' => 'Kata sandi baru harus berbeda dari yang sekarang.',
        ]);

        $user->forceFill(['password' => Hash::make($request->password)])->save();
        $request->session()->regenerate();

        return back()->with('success', 'Kata sandi berhasil diubah.');
    }
}
