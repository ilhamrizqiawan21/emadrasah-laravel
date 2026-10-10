<?php

namespace App\Http\Controllers;

use App\Models\Jadwal;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Support\AksesKelas;

class PortalGuruController extends Controller
{
    private const URUTAN_HARI = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];

    public function index()
    {
        $user = auth()->user();
        $guruId = AksesKelas::guruId($user);

        // Portal ini ringkasan pribadi guru; admin/operator memakai dashboard.
        if ($guruId === null) {
            return redirect()->route('dashboard');
        }

        $jadwal = Jadwal::with(['kelas', 'mapel'])->where('guru_id', $guruId)->where('status', 'aktif')
            ->orderBy('jam_mulai')->get()
            ->sortBy(fn ($j) => (self::URUTAN_HARI[$j->hari] ?? 9).$j->jam_mulai)->values();

        $hariIni = now()->translatedFormat('l');
        $jadwalHariIni = $jadwal->where('hari', $hariIni)->values();
        $jadwalPerHari = $jadwal->groupBy('hari');

        $kelasList = AksesKelas::kelas($user);
        $jumlahSiswa = Siswa::where('status', 'Aktif')->whereIn('kelas_id', $kelasList->pluck('id'))
            ->selectRaw('kelas_id, count(*) as total')->groupBy('kelas_id')->pluck('total', 'kelas_id');
        $mapelPerKelas = $jadwal->groupBy('kelas_id')->map(fn ($g) => $g->pluck('mapel.nama_mapel')->unique()->values());

        return view('portal.index', compact('hariIni', 'jadwalHariIni', 'jadwalPerHari', 'kelasList', 'jumlahSiswa', 'mapelPerKelas', 'guruId'));
    }

    public function kelas(Kelas $kelas)
    {
        abort_unless(AksesKelas::kelas(auth()->user())->contains('id', $kelas->id), 403, 'Anda tidak berhak melihat kelas ini.');

        $siswa = Siswa::where('kelas_id', $kelas->id)->where('status', 'Aktif')->orderBy('nama_lengkap')->get();

        return view('portal.kelas', compact('kelas', 'siswa'));
    }
}
