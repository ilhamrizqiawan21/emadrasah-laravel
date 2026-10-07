<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Task;
use App\Models\SuratMasuk;
use App\Models\AgendaGuru;
use App\Models\SaranaPrasarana;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik utama
        $totalSiswa = Siswa::count();
        $totalGuru = Guru::count();
        $totalKelas = Kelas::count();
        $taskPending = Task::where('status', '!=', 'selesai')->count();
        $suratMasukBulanIni = SuratMasuk::whereYear('tanggal_terima', now()->year)->whereMonth('tanggal_terima', now()->month)->count();
        $guruHadirHariIni = AgendaGuru::whereDate('tanggal', today())->where('status', 'hadir')->count();
        $saranaRusak = SaranaPrasarana::whereIn('kondisi', ['rusak_berat', 'rusak_ringan'])->count();

        // Cek kelengkapan buku induk (yang sudah isi NISN dan NIK)
        $siswaLengkap = Siswa::whereNotNull('nisn')->whereNotNull('nik')->count();
        $persenLengkap = $totalSiswa > 0 ? round(($siswaLengkap / $totalSiswa) * 100) : 0;

        // Data untuk grafik kehadiran 7 hari terakhir
        $kehadiran = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $hadir = AgendaGuru::whereDate('tanggal', $date)->where('status', 'hadir')->count();
            $tidakHadir = AgendaGuru::whereDate('tanggal', $date)->whereIn('status', ['izin', 'sakit', 'alpha'])->count();
            $kehadiran[] = [
                'tanggal' => $date->format('d/m'),
                'hadir' => $hadir,
                'tidak_hadir' => $tidakHadir,
            ];
        }

        // Tugas pending (5 terbaru)
        $pendingTasks = Task::where('status', '!=', 'selesai')
            ->orderByRaw("CASE prioritas WHEN 'tinggi' THEN 1 WHEN 'sedang' THEN 2 WHEN 'rendah' THEN 3 ELSE 4 END")
            ->orderBy('deadline', 'asc')
            ->limit(5)
            ->get();

        // Surat masuk terbaru (5 terakhir)
        $recentSuratMasuk = SuratMasuk::orderBy('tanggal_terima', 'desc')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalSiswa',
            'totalGuru',
            'totalKelas',
            'taskPending',
            'suratMasukBulanIni',
            'guruHadirHariIni',
            'saranaRusak',
            'persenLengkap',
            'kehadiran',
            'pendingTasks',
            'recentSuratMasuk'
        ));
    }
}
