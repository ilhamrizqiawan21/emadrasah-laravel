<?php

namespace App\Http\Controllers;

use App\Models\AgendaGuru;
use App\Models\Kelas;
use App\Models\Guru;
use App\Models\Jadwal;
use App\Models\SaranaPrasarana;
use App\Models\Siswa;
use App\Models\SuratMasuk;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        $can = fn (string $permission): bool => $user?->hasPermission($permission) ?? false;

        $totalSiswa = Siswa::count();
        $totalGuru = Guru::count();
        $totalKelas = Kelas::count();
        $taskPending = Task::where('status', '!=', 'selesai')->count();
        $suratMasukBulanIni = SuratMasuk::whereMonth('tanggal_terima', now()->month)->count();
        $guruHadirHariIni = AgendaGuru::whereDate('tanggal', today())->where('status', 'hadir')->count();
        $saranaRusak = SaranaPrasarana::whereIn('kondisi', ['rusak_berat', 'rusak_ringan'])->count();

        $siswaLengkap = Siswa::whereNotNull('nisn')->whereNotNull('nik')->count();
        $persenLengkap = $totalSiswa > 0 ? round(($siswaLengkap / $totalSiswa) * 100) : 0;
        $hariIni = now()->locale('id')->translatedFormat('l');

        $kehadiran = [];
        if ($can('absensi.view')) {
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
        }

        $jadwalHariIniQuery = Jadwal::with(['kelas:id,nama_kelas', 'mapel:id,nama_mapel', 'guru:id,nama,user_id'])
            ->where('hari', $hariIni)
            ->where('status', 'aktif')
            ->orderBy('jam_mulai');

        if ($user?->role === 'guru') {
            $jadwalHariIniQuery->whereHas('guru', fn ($query) => $query->where('user_id', $user->id));
        }

        $jadwalHariIni = $can('jadwal.view')
            ? $jadwalHariIniQuery->limit(6)->get()->map(fn (Jadwal $jadwal) => [
                'id' => $jadwal->id,
                'kelas' => $jadwal->kelas?->nama_kelas ?? '-',
                'mapel' => $jadwal->mapel?->nama_mapel ?? '-',
                'guru' => $jadwal->guru?->nama ?? '-',
                'jam' => substr((string) $jadwal->jam_mulai, 0, 5) . ' - ' . substr((string) $jadwal->jam_selesai, 0, 5),
                'ruang' => $jadwal->ruang,
            ])
            : [];

        $pendingTasks = $can('tasks.view')
            ? Task::where('status', '!=', 'selesai')
                ->when($user?->role === 'guru', fn ($query) => $query->where(function ($taskQuery) use ($user) {
                    $taskQuery->where('assigned_to', $user->id)->orWhereNull('assigned_to');
                }))
                ->orderByRaw("case prioritas when 'tinggi' then 1 when 'sedang' then 2 else 3 end")
                ->orderByRaw('deadline is null')
                ->orderBy('deadline')
                ->limit(5)
                ->get(['id', 'judul', 'prioritas', 'deadline', 'status', 'progress_persen'])
            : [];

        $recentSuratMasuk = $can('surat_masuk.view')
            ? SuratMasuk::orderBy('tanggal_terima', 'desc')
                ->limit(5)
                ->get(['id', 'nomor_agenda', 'asal_surat', 'perihal', 'tanggal_terima', 'status'])
            : [];

        $saranaBermasalah = $can('sarana.view')
            ? SaranaPrasarana::whereIn('kondisi', ['rusak_berat', 'rusak_ringan'])
                ->orderByRaw("case kondisi when 'rusak_berat' then 1 else 2 end")
                ->limit(5)
                ->get(['id', 'kode_sarana', 'nama_sarana', 'kondisi', 'stok_tersedia', 'lokasi_ruang'])
            : [];

        $attentionItems = collect([
            [
                'label' => 'Biodata siswa belum lengkap',
                'value' => $can('buku_induk.view') ? Siswa::whereNull('nisn')->orWhereNull('nik')->count() : null,
                'href' => '/buku-induk',
                'permission' => 'buku_induk.view',
            ],
            [
                'label' => 'Guru belum terhubung akun',
                'value' => $can('guru.view') ? Guru::whereNull('user_id')->count() : null,
                'href' => '/guru',
                'permission' => 'guru.view',
            ],
            [
                'label' => 'Task lewat deadline',
                'value' => $can('tasks.view') ? Task::where('status', '!=', 'selesai')->whereDate('deadline', '<', today())->count() : null,
                'href' => '/tasks',
                'permission' => 'tasks.view',
            ],
            [
                'label' => 'Surat masih diproses',
                'value' => $can('surat_masuk.view') ? SuratMasuk::where('status', 'diproses')->count() : null,
                'href' => '/surat-masuk',
                'permission' => 'surat_masuk.view',
            ],
            [
                'label' => 'Sarana perlu perbaikan',
                'value' => $can('sarana.view') ? $saranaRusak : null,
                'href' => '/sarana',
                'permission' => 'sarana.view',
            ],
        ])->filter(fn (array $item) => $can($item['permission']))->values();

        $quickActions = collect([
            ['label' => 'Tambah Siswa', 'href' => '/siswa/create', 'permission' => 'siswa.create', 'variant' => 'default'],
            ['label' => 'Input Absensi', 'href' => '/absensi', 'permission' => 'absensi.create', 'variant' => 'secondary'],
            ['label' => 'Buat Task', 'href' => '/tasks/create', 'permission' => 'tasks.create', 'variant' => 'secondary'],
            ['label' => 'Catat Surat Masuk', 'href' => '/surat-masuk/create', 'permission' => 'surat_masuk.create', 'variant' => 'secondary'],
            ['label' => 'Tambah Sarana', 'href' => '/sarana/create', 'permission' => 'sarana.create', 'variant' => 'secondary'],
            ['label' => 'Lihat Jadwal', 'href' => '/jadwal', 'permission' => 'jadwal.view', 'variant' => 'outline'],
            ['label' => 'Lihat Raport', 'href' => '/raport', 'permission' => 'raport.view', 'variant' => 'outline'],
        ])->filter(fn (array $action) => $can($action['permission']))->values();

        return Inertia::render('Dashboard/Index', [
            'role' => $user?->role,
            'stats' => [
                'total_siswa' => $can('siswa.view') ? $totalSiswa : null,
                'total_guru' => $can('guru.view') ? $totalGuru : null,
                'total_kelas' => $can('kelas.view') ? $totalKelas : null,
                'task_pending' => $can('tasks.view') ? $taskPending : null,
                'surat_masuk_bulan_ini' => $can('surat_masuk.view') ? $suratMasukBulanIni : null,
                'guru_hadir_hari_ini' => $can('absensi.view') ? $guruHadirHariIni : null,
                'sarana_rusak' => $can('sarana.view') ? $saranaRusak : null,
                'persen_buku_induk_lengkap' => $can('buku_induk.view') ? $persenLengkap : null,
            ],
            'todayLabel' => $hariIni . ', ' . now()->format('d/m/Y'),
            'kehadiran' => $kehadiran,
            'jadwalHariIni' => $jadwalHariIni,
            'pendingTasks' => $pendingTasks,
            'recentSuratMasuk' => $recentSuratMasuk,
            'saranaBermasalah' => $saranaBermasalah,
            'attentionItems' => $attentionItems,
            'quickActions' => $quickActions,
        ]);
    }
}
