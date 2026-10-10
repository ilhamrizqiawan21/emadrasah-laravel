<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\Request;

/** Admin menautkan akun wali murid/siswa ke data siswa (dasar hak akses Portal Wali). */
class UserSiswaController extends Controller
{
    private const ROLE_TERTAUT = ['wali_murid', 'siswa'];

    public function store(Request $request, User $user)
    {
        abort_unless(in_array($user->role, self::ROLE_TERTAUT, true), 404);
        $request->validate(['nis' => 'required|string|max:50']);

        $siswa = Siswa::where('nis', trim($request->nis))->first();
        if (! $siswa) {
            return back()->withErrors(['nis' => 'Siswa dengan NIS tersebut tidak ditemukan.'])->withInput();
        }

        $user->anak()->syncWithoutDetaching([$siswa->id]);

        return back()->with('success', "{$siswa->nama_lengkap} berhasil ditautkan.");
    }

    public function destroy(User $user, Siswa $siswa)
    {
        abort_unless(in_array($user->role, self::ROLE_TERTAUT, true), 404);

        $user->anak()->detach($siswa->id);

        return back()->with('success', 'Tautan siswa dilepas.');
    }
}
