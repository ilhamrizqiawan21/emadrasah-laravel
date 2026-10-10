<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    private const PESAN_TANPA_TAHUN_AKTIF = 'Belum ada tahun pelajaran aktif. Aktifkan satu di menu Tahun Pelajaran.';

    public function index()
    {
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $selectedKelas = request('kelas_id') ? Kelas::find(request('kelas_id')) : null;

        // Urutan hari yang benar
        $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        // Ambil semua jam pelajaran (urut berdasarkan sesi_ke, lalu grouping per hari)
        $jamPelajaran = JamPelajaran::orderBy('sesi_ke')->orderBy('hari')->get();

        $jadwalGrid = [];
        if ($selectedKelas) {
            $jadwals = Jadwal::with(['guru', 'mapel'])
                ->where('kelas_id', $selectedKelas->id)
                ->get();

            foreach ($jadwals as $j) {
                // Cari jam pelajaran yang cocok (dari koleksi yang sudah dimuat, bukan query per baris)
                $jam = $jamPelajaran->first(fn ($jp) => $jp->hari === $j->hari
                    && $jp->jam_mulai == $j->jam_mulai
                    && $jp->jam_selesai == $j->jam_selesai);
                if ($jam) {
                    $key = $selectedKelas->id.'_'.$j->hari.'_'.$jam->sesi_ke;
                    $jadwalGrid[$key] = [
                        'jadwal_id' => $j->id,
                        'guru_kode' => $j->guru->kode ?? '',
                        'guru_nama' => $j->guru->nama ?? '',
                        'mapel' => $j->mapel->nama_mapel ?? '',
                    ];
                }
            }
        }

        return view('jadwal.index', compact('kelasList', 'selectedKelas', 'hariList', 'jamPelajaran', 'jadwalGrid'));
    }

    public function create()
    {
        $kelas = Kelas::all();
        $gurus = Guru::all();
        $mapels = Mapel::all();
        $jamPelajaran = JamPelajaran::orderBy('hari')->orderBy('sesi_ke')->get();
        $existingJadwals = Jadwal::select('id', 'kelas_id', 'guru_id', 'hari', 'jam_mulai', 'jam_selesai', 'jam_pelajaran_id')->get();

        return view('jadwal.create', compact('kelas', 'gurus', 'mapels', 'jamPelajaran', 'existingJadwals'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:gurus,id',
            'mapel_id' => 'required|exists:mapels,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'ruang' => 'nullable|string|max:100',
            'sesi_id' => 'required|exists:jam_pelajaran,id',
        ]);

        // Ambil sesi untuk mendapatkan jam mulai & selesai
        $sesi = JamPelajaran::find($request->sesi_id);
        $jamMulai = $sesi->jam_mulai;
        $jamSelesai = $sesi->jam_selesai;

        // Conflict check
        $conflict = Jadwal::where('guru_id', $validated['guru_id'])
            ->where('hari', $validated['hari'])
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai)
            ->exists();

        if ($conflict) {
            return back()->withErrors(['guru_id' => 'Guru sudah memiliki jadwal di waktu tersebut.'])->withInput();
        }

        $tpKode = TahunPelajaran::kodeAktif();
        if (! $tpKode) {
            return back()->withErrors(['kelas_id' => self::PESAN_TANPA_TAHUN_AKTIF])->withInput();
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
            'tahun_pelajaran_kode' => $tpKode,
        ]);

        return redirect()->route('jadwal.index')->with('success', 'Jadwal berhasil ditambahkan.');
    }

    public function edit(Jadwal $jadwal)
    {
        $kelas = Kelas::all();
        $gurus = Guru::all();
        $mapels = Mapel::all();
        $jamPelajaran = JamPelajaran::orderBy('hari')->orderBy('sesi_ke')->get();
        $existingJadwals = Jadwal::where('id', '!=', $jadwal->id)->select('id', 'kelas_id', 'guru_id', 'hari', 'jam_mulai', 'jam_selesai', 'jam_pelajaran_id')->get();

        return view('jadwal.edit', compact('jadwal', 'kelas', 'gurus', 'mapels', 'jamPelajaran', 'existingJadwals'));
    }

    public function update(Request $request, Jadwal $jadwal)
    {
        $validated = $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'guru_id' => 'required|exists:gurus,id',
            'mapel_id' => 'required|exists:mapels,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'ruang' => 'nullable|string|max:100',
            'sesi_id' => 'required|exists:jam_pelajaran,id',
        ]);

        $sesi = JamPelajaran::find($request->sesi_id);
        $jamMulai = $sesi->jam_mulai;
        $jamSelesai = $sesi->jam_selesai;

        // Conflict check exclude current
        $conflict = Jadwal::where('guru_id', $validated['guru_id'])
            ->where('hari', $validated['hari'])
            ->where('id', '!=', $jadwal->id)
            ->where('jam_mulai', '<', $jamSelesai)
            ->where('jam_selesai', '>', $jamMulai)
            ->exists();

        if ($conflict) {
            return back()->withErrors(['guru_id' => 'Guru sudah memiliki jadwal di waktu tersebut.'])->withInput();
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
    public function grid()
    {
        $kelas = Kelas::orderBy('nama_kelas')->get();

        // Urutan hari yang benar
        $orderHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

        $jamPelajaran = JamPelajaran::orderBy('sesi_ke')
            ->get()
            ->sortBy(function ($item) use ($orderHari) {
                return array_search($item->hari, $orderHari);
            })
            ->values(); // reset index

        $gurus = Guru::all();
        $mapels = Mapel::all();

        $jadwalGrid = [];
        $jadwals = Jadwal::with(['guru', 'mapel'])->get();
        foreach ($jadwals as $j) {
            $jam = $jamPelajaran->first(function ($jp) use ($j) {
                return $jp->hari === $j->hari
                    && $jp->jam_mulai == $j->jam_mulai
                    && $jp->jam_selesai == $j->jam_selesai;
            });
            if ($jam) {
                $key = $j->kelas_id.'_'.$j->hari.'_'.$jam->id;
                $jadwalGrid[$key] = [
                    'jadwal_id' => $j->id,
                    'guru_id' => $j->guru_id,
                    'guru_kode' => $j->guru->kode ?? '',
                    'guru_nama' => $j->guru->nama ?? '',
                    'mapel_id' => $j->mapel_id,
                    'mapel_nama' => $j->mapel->nama_mapel ?? '',
                ];
            }
        }

        return view('jadwal.grid', compact('kelas', 'jamPelajaran', 'gurus', 'mapels', 'jadwalGrid'));
    }

    public function gridStore(Request $request)
    {
        $tpKode = TahunPelajaran::kodeAktif();
        if (! $tpKode) {
            return response()->json(['status' => 'error', 'success' => false, 'message' => self::PESAN_TANPA_TAHUN_AKTIF], 422);
        }

        // 1. Batch save
        if ($request->has('changes')) {
            $changes = $request->input('changes', []);
            $results = [];

            foreach ($changes as $item) {
                if (($item['action'] ?? '') === 'delete') {
                    if (! empty($item['jadwal_id'])) {
                        Jadwal::where('id', $item['jadwal_id'])->delete();
                    }
                    $results[] = ['success' => true, 'action' => 'delete'];

                    continue;
                }

                $kelasId = $item['kelas_id'] ?? null;
                $guruId = $item['guru_id'] ?? null;
                $mapelId = $item['mapel_id'] ?? null;
                $hari = $item['hari'] ?? null;
                $jamMulai = $item['jam_mulai'] ?? null;
                $jamSelesai = $item['jam_selesai'] ?? null;
                $jamId = $item['jam_id'] ?? null;

                if (! $kelasId || ! $guruId || ! $hari) {
                    $results[] = ['success' => false, 'message' => 'Data tidak lengkap'];

                    continue;
                }

                if (! $mapelId) {
                    $guru = Guru::find($guruId);
                    if ($guru && $guru->bidang_studi) {
                        $foundMapel = Mapel::where('nama_mapel', 'like', '%'.$guru->bidang_studi.'%')->first();
                        $mapelId = $foundMapel?->id;
                    }
                    if (! $mapelId) {
                        $mapelId = Mapel::value('id');
                    }
                }

                if (! $jamMulai || ! $jamSelesai) {
                    if ($jamId) {
                        $sesi = JamPelajaran::find($jamId);
                        if ($sesi) {
                            $jamMulai = $sesi->jam_mulai;
                            $jamSelesai = $sesi->jam_selesai;
                        }
                    }
                }

                // Cek konflik guru di kelas lain pada waktu yang sama
                $conflict = Jadwal::where('guru_id', $guruId)
                    ->where('hari', $hari)
                    ->where('kelas_id', '!=', $kelasId)
                    ->where('jam_mulai', '<', $jamSelesai)
                    ->where('jam_selesai', '>', $jamMulai)
                    ->exists();

                if ($conflict) {
                    $results[] = ['success' => false, 'message' => 'Guru sudah memiliki jadwal di kelas lain pada waktu tersebut'];

                    continue;
                }

                $jadwal = Jadwal::updateOrCreate(
                    [
                        'kelas_id' => $kelasId,
                        'hari' => $hari,
                        'jam_mulai' => $jamMulai,
                        'jam_selesai' => $jamSelesai,
                    ],
                    [
                        'guru_id' => $guruId,
                        'mapel_id' => $mapelId,
                        'jam_pelajaran_id' => $jamId,
                        'tahun_pelajaran_kode' => $tpKode,
                    ]
                );

                $results[] = ['success' => true, 'id' => $jadwal->id, 'action' => 'save'];
            }

            return response()->json([
                'success' => true,
                'results' => $results,
            ]);
        }

        // 2. Single item save
        $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_id' => 'required|integer|exists:jam_pelajaran,id',
            'guru_id' => 'required|integer|exists:gurus,id',
            'mapel_id' => 'required|integer|exists:mapels,id',
        ]);

        $sesi = JamPelajaran::findOrFail($request->jam_id);

        $conflict = Jadwal::where('guru_id', $request->guru_id)
            ->where('hari', $request->hari)
            ->where(function ($q) use ($request) {
                $q->where('kelas_id', '!=', $request->kelas_id)
                    ->orWhere('jam_pelajaran_id', '!=', $request->jam_id)
                    ->orWhereNull('jam_pelajaran_id');
            })
            ->where('jam_mulai', '<', $sesi->jam_selesai)
            ->where('jam_selesai', '>', $sesi->jam_mulai)
            ->exists();

        if ($conflict) {
            return response()->json(['status' => 'error', 'message' => 'Guru sudah memiliki jadwal di waktu tersebut.'], 422);
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
                'tahun_pelajaran_kode' => $tpKode,
            ]
        );

        return response()->json(['status' => 'success', 'jadwal' => $jadwal]);
    }

    public function resolveKode(Request $request)
    {
        $request->validate(['kode' => 'required|string']);
        $guru = Guru::where('kode', $request->kode)->first();

        if (! $guru) {
            return response()->json(['status' => 'error', 'message' => 'Guru tidak ditemukan.'], 404);
        }

        return response()->json([
            'status' => 'success',
            'guru_id' => $guru->id,
            'nama' => $guru->nama,
        ]);
    }
}
