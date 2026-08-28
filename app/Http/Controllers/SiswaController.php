<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SiswaController extends Controller
{
    public function index(Request $request): Response
    {
        $sorts = [
            'nama' => 'nama_lengkap',
            'nis' => 'nis',
            'kelas' => 'kelas_id',
            'status' => 'status',
        ];
        $sort = $sorts[$request->input('sort')] ?? 'nama_lengkap';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $siswa = Siswa::with(['kelas', 'tahunPelajaran'])
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('nama_lengkap', 'like', "%{$request->search}%")
                        ->orWhere('nis', 'like', "%{$request->search}%")
                        ->orWhere('nisn', 'like', "%{$request->search}%");
                });
            })
            ->when($request->status, fn ($q, string $status) => $q->where('status', $status))
            ->when($request->kelas_id, fn ($q, string $kelasId) => $q->where('kelas_id', $kelasId))
            ->when($request->tahun_pelajaran_id, fn ($q, string $tahunPelajaranId) => $q->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Siswa $siswa) => [
                'id' => $siswa->id,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'nama_lengkap' => $siswa->nama_lengkap,
                'jenis_kelamin' => $siswa->jenis_kelamin,
                'tempat_lahir' => $siswa->tempat_lahir,
                'tanggal_lahir' => $siswa->tanggal_lahir?->toDateString(),
                'status' => $siswa->status,
                'kelas' => $siswa->kelas ? [
                    'id' => $siswa->kelas->id,
                    'nama_kelas' => $siswa->kelas->nama_kelas,
                ] : null,
                'tahun_pelajaran' => $siswa->tahunPelajaran ? [
                    'id' => $siswa->tahunPelajaran->id,
                    'kode' => $siswa->tahunPelajaran->kode,
                ] : null,
            ]);

        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get(['id', 'nama_kelas']);
        $tahunPelajaran = TahunPelajaran::orderBy('kode')->get(['id', 'kode', 'is_aktif']);

        return Inertia::render('Siswa/Index', [
            'siswa' => $siswa,
            'kelas' => $kelas,
            'tahunPelajaran' => $tahunPelajaran,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'kelas_id' => $request->kelas_id,
                'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                'sort' => $request->sort,
                'direction' => $request->direction,
            ],
        ]);
    }

    public function create(): Response
    {
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get(['id', 'nama_kelas']);
        $tahunPelajaran = TahunPelajaran::orderBy('kode')->get(['id', 'kode', 'is_aktif']);

        return Inertia::render('Siswa/Form', [
            'kelas' => $kelas,
            'tahunPelajaran' => $tahunPelajaran,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:50', 'unique:siswa,nis'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nama_orang_tua' => ['nullable', 'string', 'max:255'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tahun_pelajaran_id' => ['nullable', 'exists:tahun_pelajaran,id'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'nisn' => ['nullable', 'string', 'max:10', 'unique:siswa,nisn'],
            'nik' => ['nullable', 'string', 'max:16', 'unique:siswa,nik'],
            'alamat' => ['nullable', 'string'],
            'hp' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:Aktif,Lulus,Pindah,Keluar'],
        ]);

        Siswa::create($validated);

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil ditambahkan.');
    }

    public function show(Siswa $siswa)
    {
        return view('siswa.show', compact('siswa'));
    }

    public function edit(Siswa $siswa): Response
    {
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get(['id', 'nama_kelas']);
        $tahunPelajaran = TahunPelajaran::orderBy('kode')->get(['id', 'kode', 'is_aktif']);

        return Inertia::render('Siswa/Form', [
            'siswa' => [
                'id' => $siswa->id,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'nik' => $siswa->nik,
                'nama_lengkap' => $siswa->nama_lengkap,
                'nama_orang_tua' => $siswa->nama_orang_tua,
                'kelas_id' => $siswa->kelas_id,
                'tahun_pelajaran_id' => $siswa->tahun_pelajaran_id,
                'jenis_kelamin' => $siswa->jenis_kelamin,
                'tempat_lahir' => $siswa->tempat_lahir,
                'tanggal_lahir' => $siswa->tanggal_lahir?->toDateString(),
                'alamat' => $siswa->alamat,
                'hp' => $siswa->hp,
                'status' => $siswa->status,
            ],
            'kelas' => $kelas,
            'tahunPelajaran' => $tahunPelajaran,
        ]);
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nis' => ['required', 'string', 'max:50', Rule::unique('siswa', 'nis')->ignore($siswa->id)],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nama_orang_tua' => ['nullable', 'string', 'max:255'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tahun_pelajaran_id' => ['nullable', 'exists:tahun_pelajaran,id'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'nisn' => ['nullable', 'string', 'max:10', Rule::unique('siswa', 'nisn')->ignore($siswa->id)],
            'nik' => ['nullable', 'string', 'max:16', Rule::unique('siswa', 'nik')->ignore($siswa->id)],
            'alamat' => ['nullable', 'string'],
            'hp' => ['nullable', 'string', 'max:20'],
            'status' => ['required', 'in:Aktif,Lulus,Pindah,Keluar'],
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
