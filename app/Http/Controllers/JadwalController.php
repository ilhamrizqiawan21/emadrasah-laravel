<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Mapel;
use App\Models\JamPelajaran;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JadwalController extends Controller
{
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
            // Cari jam pelajaran yang cocok
            $jam = JamPelajaran::where('hari', $j->hari)
                ->where('jam_mulai', $j->jam_mulai)
                ->where('jam_selesai', $j->jam_selesai)
                ->first();
            if ($jam) {
                $key = $selectedKelas->id . '_' . $j->hari . '_' . $jam->sesi_ke;
                $jadwalGrid[$key] = [
                    'jadwal_id' => $j->id,
                    'guru_kode' => $j->guru->kode ?? '',
                    'guru_nama' => $j->guru->nama ?? '',
                    'mapel'     => $j->mapel->nama_mapel ?? '',
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
        ]);

        // Ambil sesi untuk mendapatkan jam mulai & selesai
        $sesi = JamPelajaran::find($request->sesi_id);
        $jamMulai = $sesi->jam_mulai;
        $jamSelesai = $sesi->jam_selesai;

        // Conflict check
        $conflict = Jadwal::where('guru_id', $validated['guru_id'])
            ->where('hari', $validated['hari'])
            ->where(function($q) use ($jamMulai, $jamSelesai) {
                $q->whereBetween('jam_mulai', [$jamMulai, $jamSelesai])
                  ->orWhereBetween('jam_selesai', [$jamMulai, $jamSelesai]);
            })->exists();

        if ($conflict) {
            return back()->withErrors(['guru_id' => 'Guru sudah memiliki jadwal di waktu tersebut.'])->withInput();
        }

        $tpKode = TahunPelajaran::where('is_aktif', true)->first()->kode ?? '2025/2026';

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
        ]);

        $sesi = JamPelajaran::find($request->sesi_id);
        $jamMulai = $sesi->jam_mulai;
        $jamSelesai = $sesi->jam_selesai;

        // Conflict check exclude current
        $conflict = Jadwal::where('guru_id', $validated['guru_id'])
            ->where('hari', $validated['hari'])
            ->where('id', '!=', $jadwal->id)
            ->where(function($q) use ($jamMulai, $jamSelesai) {
                $q->whereBetween('jam_mulai', [$jamMulai, $jamSelesai])
                  ->orWhereBetween('jam_selesai', [$jamMulai, $jamSelesai]);
            })->exists();

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
            $key = $j->kelas_id . '_' . $j->hari . '_' . $jam->id;
            $jadwalGrid[$key] = [
                'jadwal_id' => $j->id,
                'guru_id'   => $j->guru_id,
                'guru_kode' => $j->guru->kode ?? '',
                'mapel_id'  => $j->mapel_id,
            ];
        }
    }

    return view('jadwal.grid', compact('kelas', 'jamPelajaran', 'gurus', 'mapels', 'jadwalGrid'));
}

public function gridStore(Request $request)
{
    $request->validate([
        'kelas_id' => 'required|integer',
        'hari' => 'required|string',
        'jam_id' => 'required|integer',
        'guru_id' => 'required|integer',
        'mapel_id' => 'required|integer',
    ]);

    $sesi = JamPelajaran::findOrFail($request->jam_id);

    // Cek konflik guru
    $conflict = Jadwal::where('guru_id', $request->guru_id)
        ->where('hari', $request->hari)
        ->where(function($q) use ($sesi) {
            $q->whereBetween('jam_mulai', [$sesi->jam_mulai, $sesi->jam_selesai])
              ->orWhereBetween('jam_selesai', [$sesi->jam_mulai, $sesi->jam_selesai]);
        })->exists();

    if ($conflict) {
        return response()->json(['status' => 'error', 'message' => 'Guru sudah memiliki jadwal di waktu tersebut.'], 422);
    }

    // Cek apakah sudah ada jadwal di slot tersebut -> update atau create
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
            'tahun_pelajaran_kode' => TahunPelajaran::where('is_aktif', true)->first()->kode ?? '2025/2026',
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
