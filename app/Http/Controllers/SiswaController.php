<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index(Request $request)
    {
        $siswa = Siswa::with('kelas')
            ->when($request->search, function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                    ->orWhere('nis', 'like', "%{$request->search}%")
                    ->orWhere('nisn', 'like', "%{$request->search}%");
            })
            ->orderBy('nama_lengkap')
            ->orderBy('id')
            ->paginate(15);

        return view('siswa.index', compact('siswa'));
    }

    public function create()
    {
        $kelas = Kelas::all();
        $tahunPelajaran = TahunPelajaran::all();

        return view('siswa.create', compact('kelas', 'tahunPelajaran'));
    }

    /**
     * Pencarian siswa untuk kotak isian otomatis (mis. form surat keluar). Hasil dibatasi 10 baris
     * supaya halaman tetap ringan walau siswanya puluhan ribu; minimal 2 karakter.
     */
    public function cari(Request $request)
    {
        $q = trim((string) $request->query('q', ''));

        if (mb_strlen($q) < 2) {
            return response()->json([]);
        }

        // "!" sebagai karakter escape: valid di MySQL dan SQLite (backslash hanya bekerja di MySQL).
        $like = '%'.preg_replace('/([%_!])/', '!$1', $q).'%';

        return response()->json(
            Siswa::select('id', 'nama_lengkap', 'nis', 'nisn')
                ->where(fn ($w) => $w
                    ->whereRaw("nama_lengkap LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("nis LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("nisn LIKE ? ESCAPE '!'", [$like]))
                ->orderBy('nama_lengkap')
                ->limit(10)
                ->get()
                ->map(fn ($s) => ['id' => $s->id, 'label' => "{$s->nama_lengkap} (NIS: {$s->nis})"])
        );
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
            'nik' => 'nullable|digits:16|unique:siswa,nik',
            'alamat' => 'nullable',
            'hp' => 'nullable',
            'status' => 'required|in:Aktif,Lulus,Pindah,Keluar',
            'tahun_pelajaran_id' => 'nullable|exists:tahun_pelajaran,id',
        ]);

        Siswa::create($validated);

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
            'nis' => 'required|unique:siswa,nis,'.$siswa->id,
            'nama_lengkap' => 'required',
            'nama_orang_tua' => 'nullable|string|max:255',
            'kelas_id' => 'required|exists:kelas,id',
            'jenis_kelamin' => 'required|in:L,P',
            'tempat_lahir' => 'nullable',
            'tanggal_lahir' => 'nullable|date',
            'nisn' => 'nullable|unique:siswa,nisn,'.$siswa->id,
            'nik' => 'nullable|digits:16|unique:siswa,nik,'.$siswa->id,
            'alamat' => 'nullable',
            'hp' => 'nullable',
            'status' => 'required|in:Aktif,Lulus,Pindah,Keluar',
            'tahun_pelajaran_id' => 'nullable|exists:tahun_pelajaran,id',
        ]);

        $siswa->update($validated);

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus.');
    }
}
