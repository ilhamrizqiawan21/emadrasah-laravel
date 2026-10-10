<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::with('guruPembimbing')->orderBy('tingkat')->orderBy('nama_kelas')->orderBy('id')->paginate(10);
        $gurus = Guru::orderBy('nama')->get();

        return view('kelas.index', compact('kelas', 'gurus'));
    }

    public function create()
    {
        $gurus = Guru::orderBy('nama')->get();

        return view('kelas.create', compact('gurus'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|unique:kelas',
            'tingkat' => 'required',
            'guru_pembimbing_id' => 'nullable|exists:gurus,id',
            'kapasitas' => 'nullable|integer|min:1',
            'ruangan' => 'nullable|string|max:100',
            'fase' => 'nullable|string|max:5',
        ]);
        Kelas::create($validated);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas)
    {
        $gurus = Guru::orderBy('nama')->get();

        return view('kelas.edit', compact('kelas', 'gurus'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'nama_kelas' => 'required|unique:kelas,nama_kelas,'.$kelas->id,
            'tingkat' => 'required',
            'guru_pembimbing_id' => 'nullable|exists:gurus,id',
            'kapasitas' => 'nullable|integer|min:1',
            'ruangan' => 'nullable|string|max:100',
            'fase' => 'nullable|string|max:5',
        ]);
        $kelas->update($validated);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diupdate.');
    }

    public function destroy($id)
    {
        try {
            $kelas = Kelas::find($id);
            if (! $kelas) {
                return redirect()->route('kelas.index')->with('error', 'Kelas tidak ditemukan.');
            }

            // Cek apakah kelas memiliki jadwal
            $jadwalCount = DB::table('jadwals')->where('kelas_id', $id)->count();
            if ($jadwalCount > 0) {
                return redirect()->route('kelas.index')->with('error', "Kelas masih memiliki {$jadwalCount} jadwal. Hapus jadwal terlebih dahulu.");
            }

            // Siswa terhubung dengan cascade; tanpa pemeriksaan ini seluruh siswa kelas ikut terhapus.
            // Siswa yang dihapus lunak (arsip) tetap dihitung: barisnya masih ada dan ikut terhapus permanen oleh cascade.
            $siswaCount = DB::table('siswa')->where('kelas_id', $id)->count();
            if ($siswaCount > 0) {
                return redirect()->route('kelas.index')->with('error', "Kelas masih terhubung dengan {$siswaCount} siswa (termasuk yang sudah dihapus/arsip). Pindahkan siswa ke kelas lain terlebih dahulu.");
            }

            // Lewat model (bukan query builder) agar penghapusan tercatat di audit log.
            if ($kelas->delete()) {
                return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
            } else {
                return redirect()->route('kelas.index')->with('error', 'Gagal menghapus kelas. Data tidak ditemukan.');
            }
        } catch (\Exception $e) {
            \Log::error('Hapus kelas gagal: '.$e->getMessage());

            return redirect()->route('kelas.index')->with('error', 'Kelas tidak dapat dihapus karena masih dipakai data lain (mis. siswa).');
        }
    }
}
