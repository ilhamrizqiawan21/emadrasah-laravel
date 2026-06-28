<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $siswa = Siswa::with('kelas')
            ->when($request->search, function($q) use ($request) {
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                  ->orWhere('nis', 'like', "%{$request->search}%")
                  ->orWhere('nisn', 'like', "%{$request->search}%");
            })
            ->orderBy('nama_lengkap')
            ->paginate(15);
            
        return view('siswa.index', compact('siswa'));
    }

    public function create()
    {
        $kelas = Kelas::all();
        $tahunPelajaran = TahunPelajaran::all();
        return view('siswa.create', compact('kelas', 'tahunPelajaran'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|unique:siswa,nis',
            'nama_lengkap' => 'required',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable',
            'tanggal_lahir' => 'nullable|date',
            'nisn' => 'nullable|unique:siswa,nisn',
            'nik' => 'nullable|unique:siswa,nik',
            'alamat' => 'nullable',
            'hp' => 'nullable',
            'status' => 'required|in:Aktif,Lulus,Pindah,Keluar',
        ]);

        Siswa::create($validated + ['tahun_pelajaran_id' => $request->tahun_pelajaran_id]);

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function show(Siswa $siswa)
    {
        return view('siswa.show', compact('siswa'));
    }

    public function edit(Siswa $siswa)
    {
        $kelas = Kelas::all();
        $tahunPelajaran = TahunPelajaran::all();
        return view('siswa.edit', compact('siswa', 'kelas', 'tahunPelajaran'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nis' => 'required|unique:siswa,nis,' . $siswa->id,
            'nama_lengkap' => 'required',
            'nama_orang_tua' => 'nullable|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable',
            'tanggal_lahir' => 'nullable|date',
            'nisn' => 'nullable|unique:siswa,nisn,' . $siswa->id,
            'nik' => 'nullable|unique:siswa,nik,' . $siswa->id,
            'alamat' => 'nullable',
            'hp' => 'nullable',
            'status' => 'required|in:Aktif,Lulus,Pindah,Keluar',
        ]);

        $siswa->update($validated + ['tahun_pelajaran_id' => $request->tahun_pelajaran_id]);

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus.');
    }
}
