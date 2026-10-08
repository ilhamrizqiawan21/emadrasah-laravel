<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Mapel;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function index()
    {
        $gurus = Guru::orderByRaw('LENGTH(kode), kode')->paginate(10);
        return view('guru.index', compact('gurus'));
    }

public function create()
{
    $mapels = Mapel::all();
    return view('guru.create', compact('mapels'));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:gurus',
            'nama' => 'required|string|max:255',
            'bidang_studi' => 'nullable|string|max:255',
            'nip' => 'nullable|string|unique:gurus,nip',
            'email' => 'nullable|email|unique:gurus,email',
            'phone' => 'nullable|string|max:20',
            'beban_jp' => 'nullable|integer|min:0',
        ]);

        Guru::create($validated);
        return redirect()->route('guru.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru)
    {
        $mapels = Mapel::orderBy('nama_mapel')->get();
        return view('guru.edit', compact('guru', 'mapels'));
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'kode' => 'required|string|unique:gurus,kode,' . $guru->id,
            'nama' => 'required|string|max:255',
            'bidang_studi' => 'nullable|string|max:255',
            'nip' => 'nullable|string|unique:gurus,nip,' . $guru->id,
            'email' => 'nullable|email|unique:gurus,email,' . $guru->id,
            'phone' => 'nullable|string|max:20',
            'beban_jp' => 'nullable|integer|min:0',
        ]);

        $guru->update($validated);
        return redirect()->route('guru.index')->with('success', 'Guru berhasil diupdate.');
    }

    public function destroy(Guru $guru)
    {
        // Cek apakah guru masih memiliki jadwal atau relasi lain
        if ($guru->jadwals()->count() > 0) {
            return redirect()->route('guru.index')->with('error', 'Guru masih memiliki jadwal mengajar. Hapus jadwal terlebih dahulu.');
        }
        if ($guru->agendas()->count() > 0) {
            return redirect()->route('guru.index')->with('error', 'Guru masih memiliki riwayat absensi. Hapus terlebih dahulu.');
        }
        $guru->delete();
        return redirect()->route('guru.index')->with('success', 'Guru berhasil dihapus.');
    }
}
