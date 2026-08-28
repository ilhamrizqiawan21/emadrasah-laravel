<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Mapel;
use App\Models\JamPelajaran;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JadwalController extends Controller
{
    private const HARI_LIST = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

    private function activeTahunPelajaranKode(): string
    {
        return TahunPelajaran::where('is_aktif', true)->value('kode')
            ?? TahunPelajaran::latest('id')->value('kode')
            ?? '0000/0000';
    }

    private function hasGuruConflict(int $guruId, string $hari, string $jamMulai, string $jamSelesai, ?int $excludeId = null): bool
    {
        return Jadwal::where('guru_id', $guruId)
            ->where('hari', $hari)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai)
            ->exists();
    }

    private function hasKelasConflict(int $kelasId, string $hari, string $jamMulai, string $jamSelesai, ?int $excludeId = null): bool
    {
        return Jadwal::where('kelas_id', $kelasId)
            ->where('hari', $hari)
            ->when($excludeId, fn ($query) => $query->where('id', '!=', $excludeId))
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai)
            ->exists();
    }

    public function index(Request $request): Response
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']);
        $selectedKelas = $request->kelas_id ? Kelas::find($request->kelas_id) : null;
        $jamPelajaran = JamPelajaran::orderBy('sesi_ke')->orderBy('hari')->get();

        $jadwalGrid = [];
        if ($selectedKelas) {
            $jadwals = Jadwal::with(['guru', 'mapel'])
                ->where('kelas_id', $selectedKelas->id)
                ->get();

            foreach ($jadwals as $jadwal) {
                $jam = $jamPelajaran->first(function (JamPelajaran $item) use ($jadwal) {
                    return $item->hari === $jadwal->hari
                        && $item->jam_mulai == $jadwal->jam_mulai
                        && $item->jam_selesai == $jadwal->jam_selesai;
                });

                if ($jam) {
                    $jadwalGrid[$jadwal->hari.'_'.$jam->sesi_ke] = [
                        'jadwal_id' => $jadwal->id,
                        'guru_kode' => $jadwal->guru->kode ?? '',
                        'guru_nama' => $jadwal->guru->nama ?? '',
                        'mapel' => $jadwal->mapel->nama_mapel ?? '',
                        'ruang' => $jadwal->ruang,
                    ];
                }
            }
        }

        return Inertia::render('Jadwal/Index', [
            'kelasList' => $kelasList,
            'selectedKelas' => $selectedKelas ? [
                'id' => $selectedKelas->id,
                'nama_kelas' => $selectedKelas->nama_kelas,
            ] : null,
            'hariList' => self::HARI_LIST,
            'sesiList' => $jamPelajaran
                ->groupBy('sesi_ke')
                ->sortKeys()
                ->map(function ($items, $sesiKe) {
                    $first = $items->first();

                    return [
                        'sesi_ke' => (int) $sesiKe,
                        'jam_mulai' => substr((string) $first->jam_mulai, 0, 5),
                        'jam_selesai' => substr((string) $first->jam_selesai, 0, 5),
                        'hari' => $items->pluck('hari')->values(),
                    ];
                })
                ->values(),
            'jadwalGrid' => $jadwalGrid,
            'filters' => [
                'kelas_id' => $request->kelas_id,
            ],
        ]);
    }

    public function create()
    {
        $kelas = Kelas::all();
        $gurus = Guru::all();
        $mapels = Mapel::all();
        $jamPelajaran = JamPelajaran::orderBy('hari')->orderBy('sesi_ke')->get();
        return view('jadwal.create', compact('kelas', 'gurus', 'mapels', 'jamPelajaran'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:gurus,id',
            'mapel_id' => 'required|exists:mapels,id',
            'hari' => 'required',
            'ruang' => 'nullable',
            'sesi_id' => 'required|exists:jam_pelajaran,id',
            'semester' => 'nullable|integer|in:1,2',
            'tahun_pelajaran_kode' => 'nullable|string|exists:tahun_pelajaran,kode',
        ]);

        // Ambil sesi untuk mendapatkan jam mulai & selesai
        $sesi = JamPelajaran::find($request->sesi_id);
        $jamMulai = $sesi->jam_mulai;
        $jamSelesai = $sesi->jam_selesai;

        if ($this->hasGuruConflict((int) $validated['guru_id'], $validated['hari'], $jamMulai, $jamSelesai)) {
            return back()->withErrors(['guru_id' => 'Guru sudah memiliki jadwal di waktu tersebut.'])->withInput();
        }

        if ($this->hasKelasConflict((int) $validated['kelas_id'], $validated['hari'], $jamMulai, $jamSelesai)) {
            return back()->withErrors(['kelas_id' => 'Kelas sudah memiliki jadwal di waktu tersebut.'])->withInput();
        }

        // Simpan
        Jadwal::create([
            'kelas_id' => $validated['kelas_id'],
            'guru_id' => $validated['guru_id'],
            'mapel_id' => $validated['mapel_id'],
            'jam_pelajaran_id' => $request->sesi_id,
            'hari' => $validated['hari'],
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'ruang' => $validated['ruang'],
            'semester' => $validated['semester'] ?? 1,
            'tahun_pelajaran_kode' => $validated['tahun_pelajaran_kode'] ?? $this->activeTahunPelajaranKode(),
        ]);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Jadwal $jadwal)
    {
        $kelas = Kelas::all();
        $gurus = Guru::all();
        $mapels = Mapel::all();
        $jamPelajaran = JamPelajaran::orderBy('hari')->orderBy('sesi_ke')->get();
        return view('jadwal.edit', compact('jadwal', 'kelas', 'gurus', 'mapels', 'jamPelajaran'));
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:gurus,id',
            'mapel_id' => 'required|exists:mapels,id',
            'hari' => 'required',
            'ruang' => 'nullable',
            'sesi_id' => 'required|exists:jam_pelajaran,id',
            'semester' => 'nullable|integer|in:1,2',
            'tahun_pelajaran_kode' => 'nullable|string|exists:tahun_pelajaran,kode',
        ]);

        $sesi = JamPelajaran::find($request->sesi_id);
        $jamMulai = $sesi->jam_mulai;
        $jamSelesai = $sesi->jam_selesai;

        if ($this->hasGuruConflict((int) $validated['guru_id'], $validated['hari'], $jamMulai, $jamSelesai, $jadwal->id)) {
            return back()->withErrors(['guru_id' => 'Guru sudah memiliki jadwal di waktu tersebut.'])->withInput();
        }

        if ($this->hasKelasConflict((int) $validated['kelas_id'], $validated['hari'], $jamMulai, $jamSelesai, $jadwal->id)) {
            return back()->withErrors(['kelas_id' => 'Kelas sudah memiliki jadwal di waktu tersebut.'])->withInput();
        }

        $jadwal->update([
            'kelas_id' => $validated['kelas_id'],
            'guru_id' => $validated['guru_id'],
            'mapel_id' => $validated['mapel_id'],
            'jam_pelajaran_id' => $request->sesi_id,
            'hari' => $validated['hari'],
            'jam_mulai' => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'ruang' => $validated['ruang'],
            'semester' => $validated['semester'] ?? $jadwal->semester,
            'tahun_pelajaran_kode' => $validated['tahun_pelajaran_kode'] ?? $jadwal->tahun_pelajaran_kode,
        ]);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil diupdate.');
    }

    public function destroy(Jadwal $jadwal)
    {
        $jadwal->delete();
        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil dihapus.');
    }

    public function byKelas(Kelas $kelas)
    {
        $jadwals = Jadwal::with(['guru', 'mapel'])->where('kelas_id', $kelas->id)->orderBy('hari')->orderBy('jam_mulai')->get();
        return response()->json($jadwals);
    }

    // ========== GRID METHODS ==========
    public function grid(): Response
    {
        $kelas = Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']);

        $jamPelajaran = JamPelajaran::orderBy('sesi_ke')
            ->get(['id', 'hari', 'sesi_ke', 'jam_mulai', 'jam_selesai'])
            ->sortBy(function (JamPelajaran $item) {
                return array_search($item->hari, self::HARI_LIST, true);
            })
            ->values();

        $gurus = Guru::orderBy('kode')->get(['id', 'kode', 'nama', 'bidang_studi']);
        $mapels = Mapel::orderBy('urut')->orderBy('nama_mapel')->get(['id', 'nama_mapel']);

        $jadwalGrid = [];
        $jadwals = Jadwal::with(['guru:id,kode,nama', 'mapel:id,nama_mapel'])->get();
        foreach ($jadwals as $jadwal) {
            $jam = $jamPelajaran->first(function (JamPelajaran $item) use ($jadwal) {
                return $item->hari === $jadwal->hari
                    && $item->jam_mulai == $jadwal->jam_mulai
                    && $item->jam_selesai == $jadwal->jam_selesai;
            });

            if ($jam) {
                $jadwalGrid[$jadwal->kelas_id.'_'.$jadwal->hari.'_'.$jam->id] = [
                    'jadwal_id' => $jadwal->id,
                    'guru_id' => $jadwal->guru_id,
                    'guru_kode' => $jadwal->guru->kode ?? '',
                    'guru_nama' => $jadwal->guru->nama ?? '',
                    'mapel_id' => $jadwal->mapel_id,
                    'mapel' => $jadwal->mapel->nama_mapel ?? '',
                ];
            }
        }

        return Inertia::render('Jadwal/Grid', [
            'kelas' => $kelas,
            'jamPelajaran' => $jamPelajaran->map(fn (JamPelajaran $jam) => [
                'id' => $jam->id,
                'hari' => $jam->hari,
                'sesi_ke' => $jam->sesi_ke,
                'jam_mulai' => substr((string) $jam->jam_mulai, 0, 5),
                'jam_selesai' => substr((string) $jam->jam_selesai, 0, 5),
            ]),
            'gurus' => $gurus,
            'mapels' => $mapels,
            'jadwalGrid' => $jadwalGrid,
        ]);
    }

    public function gridStore(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'hari' => 'required|string',
            'jam_id' => 'required|exists:jam_pelajaran,id',
            'guru_id' => 'required|exists:gurus,id',
            'mapel_id' => 'required|exists:mapels,id',
            'semester' => 'nullable|integer|in:1,2',
            'tahun_pelajaran_kode' => 'nullable|string|exists:tahun_pelajaran,kode',
        ]);

        $sesi = JamPelajaran::findOrFail($request->jam_id);
        $existing = Jadwal::where('kelas_id', $request->kelas_id)
            ->where('hari', $request->hari)
            ->where('jam_pelajaran_id', $request->jam_id)
            ->first();

        if ($this->hasGuruConflict((int) $request->guru_id, $request->hari, $sesi->jam_mulai, $sesi->jam_selesai, $existing?->id)) {
            return response()->json(['status' => 'error', 'message' => 'Guru sudah memiliki jadwal di waktu tersebut.'], 422);
        }

        if ($this->hasKelasConflict((int) $request->kelas_id, $request->hari, $sesi->jam_mulai, $sesi->jam_selesai, $existing?->id)) {
            return response()->json(['status' => 'error', 'message' => 'Kelas sudah memiliki jadwal di waktu tersebut.'], 422);
        }

        $jadwal = Jadwal::updateOrCreate(
            [
                'kelas_id' => $request->kelas_id,
                'hari' => $request->hari,
                'jam_pelajaran_id' => $request->jam_id,
            ],
            [
                'guru_id' => $request->guru_id,
                'mapel_id' => $request->mapel_id,
                'jam_mulai' => $sesi->jam_mulai,
                'jam_selesai' => $sesi->jam_selesai,
                'semester' => $request->semester ?? 1,
                'tahun_pelajaran_kode' => $request->tahun_pelajaran_kode ?? $this->activeTahunPelajaranKode(),
            ]
        );

        return response()->json(['status' => 'success', 'jadwal' => $jadwal]);
    }

    public function resolveKode(Request $request)
    {
        $request->validate(['kode' => 'required|string']);
        $guru = Guru::where('kode', $request->kode)->first();

        if (!$guru) {
            return response()->json(['status' => 'error', 'message' => 'Guru tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'guru_id' => $guru->id,
            'nama' => $guru->nama,
        ]);
    }

}
