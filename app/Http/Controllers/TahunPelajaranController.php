<?php

namespace App\Http\Controllers;

use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class TahunPelajaranController extends Controller
{
    public function index()
    {
        $tahunPelajaran = TahunPelajaran::orderBy('kode')->paginate(20);
        return view('tahun-pelajaran.index', compact('tahunPelajaran'));
    }

    public function create()
    {
        return view('tahun-pelajaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:tahun_pelajaran,kode',
            'nama' => 'required|string|max:255',
            'is_aktif' => 'sometimes|boolean',
        ]);

        $tp = TahunPelajaran::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'is_aktif' => $request->has('is_aktif'),
        ]);
        $this->hanyaSatuAktif($tp);

        return redirect()->route('tahun-pelajaran.index')->with('success', 'Tahun pelajaran berhasil ditambahkan.');
    }

    public function edit(TahunPelajaran $tahunPelajaran)
    {
        return view('tahun-pelajaran.edit', compact('tahunPelajaran'));
    }

    public function update(Request $request, TahunPelajaran $tahunPelajaran)
    {
        $validated = $request->validate([
            'kode' => 'required|string|max:50|unique:tahun_pelajaran,kode,' . $tahunPelajaran->id,
            'nama' => 'required|string|max:255',
            'is_aktif' => 'sometimes|boolean',
        ]);

        $tahunPelajaran->update([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'is_aktif' => $request->has('is_aktif'),
        ]);
        $this->hanyaSatuAktif($tahunPelajaran);

        return redirect()->route('tahun-pelajaran.index')->with('success', 'Tahun pelajaran berhasil diupdate.');
    }

    /** Jadwal & raport memakai tahun pelajaran aktif pertama; pastikan hanya ada satu. */
    private function hanyaSatuAktif(TahunPelajaran $tp): void
    {
        if ($tp->is_aktif) {
            TahunPelajaran::where('id', '!=', $tp->id)->update(['is_aktif' => false]);
        }
    }

    public function destroy(TahunPelajaran $tahunPelajaran)
    {
        $tahunPelajaran->delete();
        return redirect()->route('tahun-pelajaran.index')->with('success', 'Tahun pelajaran berhasil dihapus.');
    }
}
