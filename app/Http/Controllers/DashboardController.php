<?php

namespace App\Http\Controllers;

use App\Models\AgendaGuru;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\SaranaPrasarana;
use App\Models\Siswa;
use App\Models\SuratMasuk;
use App\Models\Task;
use App\Support\PerluTindakan;

class DashboardController extends Controller
{
    public function index()
    {
        // Wali murid dan siswa tidak boleh melihat statistik madrasah; mereka punya portal sendiri.
        if (in_array(auth()->user()->role, ['wali_murid', 'siswa'], true)) {
            return redirect()->route('wali.index');
        }

        // Daftar pekerjaan hari ini hanya untuk staf TU; guru melihat portal mereka sendiri.
        $perluTindakan = in_array(auth()->user()->role, ['admin', 'operator'], true) ? PerluTindakan::untuk(auth()->user()) : null;

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
        // Satu query terkelompok untuk 7 hari (bukan 2 query per hari).
        $perHari = AgendaGuru::whereDate('tanggal', '>=', today()->subDays(6))
            ->selectRaw('tanggal, status, count(*) as total')->groupBy('tanggal', 'status')->get()
            ->groupBy(fn ($r) => $r->tanggal->format('Y-m-d'));

        $kehadiran = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);
            $baris = $perHari->get($date->format('Y-m-d'), collect());
            $hadir = (int) $baris->where('status', 'hadir')->sum('total');
            $tidakHadir = (int) $baris->whereIn('status', ['izin', 'sakit', 'alpha'])->sum('total');
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
            'recentSuratMasuk',
            'perluTindakan'
        ));
    }
}
