<?php

use App\Http\Controllers\AbsensiSiswaController;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\AgendaGuruController;
use App\Http\Controllers\ArsipAkademikController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BrandingController;
use App\Http\Controllers\BukuIndukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EksporController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\ImporController;
use App\Http\Controllers\IzinGuruController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JamPelajaranController;
use App\Http\Controllers\KategoriSaranaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\KenaikanKelasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\NilaiController;
use App\Http\Controllers\NotifikasiController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PortalGuruController;
use App\Http\Controllers\PortalWaliController;
use App\Http\Controllers\RaportController;
use App\Http\Controllers\SaranaController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\TahunPelajaranController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TemplateSuratController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserSiswaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==================== GUEST ROUTES (TIDAK PERLU LOGIN) ====================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:login');
// Logout akan di dalam grup auth (karena butuh login dulu)

// Lupa kata sandi (tamu). Nama route 'password.reset' dipakai notifikasi bawaan Laravel.
Route::middleware('guest')->group(function () {
    Route::get('/lupa-sandi', [PasswordResetController::class, 'form'])->name('password.request');
    Route::post('/lupa-sandi', [PasswordResetController::class, 'kirim'])->middleware('throttle:lupa-sandi')->name('password.email');
    Route::get('/reset-sandi/{token}', [PasswordResetController::class, 'formReset'])->name('password.reset');
    Route::post('/reset-sandi', [PasswordResetController::class, 'simpan'])->middleware('throttle:reset-sandi')->name('password.update');
});

// Logo & favicon madrasah: publik karena dipakai halaman login dan tab browser.
Route::get('/branding/{type}', [BrandingController::class, 'show'])
    ->whereIn('type', ['logo', 'favicon'])
    ->name('branding.show');

// ==================== PROTECTED ROUTES (WAJIB LOGIN) ====================
Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Kotak masuk notifikasi pribadi: semua role, hanya milik sendiri.
    Route::get('/notifikasi', [NotifikasiController::class, 'index'])->name('notifikasi.index');
    Route::post('/notifikasi/baca-semua', [NotifikasiController::class, 'bacaSemua'])->name('notifikasi.baca-semua');
    Route::post('/notifikasi/{id}/baca', [NotifikasiController::class, 'baca'])->name('notifikasi.baca');

    // Akun sendiri: semua role boleh mengganti kata sandinya.
    Route::get('/akun/sandi', [AkunController::class, 'sandi'])->name('akun.sandi');
    Route::put('/akun/sandi', [AkunController::class, 'ubahSandi'])->name('akun.sandi.update');

    // Dashboard (halaman utama setelah login)
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ========== ABSENSI GURU & GURU PENGGANTI ==========
    // Menu ini dapat diakses guru untuk mengisi absensi sendiri, selain admin/operator.
    Route::prefix('absensi')->name('absensi.')->middleware('role:admin,operator,guru')->group(function () {
        Route::get('/', [AgendaGuruController::class, 'index'])->name('index');
        Route::post('/', [AgendaGuruController::class, 'store'])->name('store');
        Route::get('rekap', [AgendaGuruController::class, 'rekap'])->name('rekap');
        Route::get('rekap/export', [AgendaGuruController::class, 'exportPdf'])->name('export-pdf');
        Route::get('pengganti/{agenda}', [AgendaGuruController::class, 'pengganti'])->name('pengganti');
        Route::post('pengganti/{agenda}', [AgendaGuruController::class, 'storePengganti'])->name('store-pengganti');
    });

    // ========== ABSENSI SISWA HARIAN ==========
    // Guru dibatasi ke kelas yang dia walikan atau ajar (dicek di controller).
    Route::prefix('absensi-siswa')->name('absensi-siswa.')->middleware('role:admin,operator,guru')->group(function () {
        Route::get('/', [AbsensiSiswaController::class, 'index'])->name('index');
        Route::post('/', [AbsensiSiswaController::class, 'store'])->name('store');
        Route::get('rekap', [AbsensiSiswaController::class, 'rekap'])->name('rekap');
    });

    // ========== INPUT NILAI PER KELAS x MAPEL ==========
    // Guru dibatasi ke kelas dan mapel di jadwalnya (dicek di controller).
    Route::prefix('nilai')->name('nilai.')->middleware('role:admin,operator,guru')->group(function () {
        Route::get('/', [NilaiController::class, 'index'])->name('index');
        Route::post('/', [NilaiController::class, 'store'])->name('store');
    });

    // ========== PORTAL GURU ==========
    Route::prefix('portal')->name('portal.')->middleware('role:admin,operator,guru')->group(function () {
        Route::get('/', [PortalGuruController::class, 'index'])->name('index');
        Route::get('kelas/{kelas}', [PortalGuruController::class, 'kelas'])->name('kelas');
    });

    // Pengajuan izin/cuti guru: guru mengajukan, admin/operator melihat semua. Keputusan ada di grup manajemen.
    Route::prefix('izin-guru')->name('izin-guru.')->middleware('role:admin,operator,guru')->group(function () {
        Route::get('/', [IzinGuruController::class, 'index'])->name('index');
        Route::get('buat', [IzinGuruController::class, 'create'])->name('create');
        Route::post('/', [IzinGuruController::class, 'store'])->name('store');
        Route::delete('{izin}', [IzinGuruController::class, 'destroy'])->name('destroy');
    });

    // ========== PORTAL WALI MURID / SISWA ==========
    // Hanya data siswa yang ditautkan ke akun (lihat PortalWaliController).
    Route::prefix('wali')->name('wali.')->middleware('role:wali_murid,siswa')->group(function () {
        Route::get('/', [PortalWaliController::class, 'index'])->name('index');
        Route::get('{siswa}', [PortalWaliController::class, 'show'])->name('show');
        Route::get('{siswa}/raport', [PortalWaliController::class, 'raport'])->name('raport');
    });

    // ========== MENU MANAJEMEN (khusus admin & operator) ==========
    Route::middleware('role:admin,operator')->group(function () {

        // ========== BERKAS UPLOAD (privat, wajib login) ==========
        Route::get('files/{path}', [FileController::class, 'show'])->where('path', '.*')->name('files.show');

        // ========== MASTER DATA ==========
        Route::post('persetujuan-izin/{izin}', [IzinGuruController::class, 'putuskan'])->name('persetujuan-izin.putuskan');
        Route::resource('guru', GuruController::class)->except('show');
        Route::resource('kelas', KelasController::class)->parameters(['kelas' => 'kelas'])->except('show');
        Route::resource('mapel', MapelController::class)->except('show');
        Route::resource('jam-pelajaran', JamPelajaranController::class)->except('show');
        Route::resource('tahun-pelajaran', TahunPelajaranController::class)->except('show');

        // ========== JADWAL PELAJARAN ==========
        Route::get('jadwal/grid', [JadwalController::class, 'grid'])->name('jadwal.grid');
        Route::post('jadwal/grid-store', [JadwalController::class, 'gridStore'])->name('jadwal.grid-store');
        Route::get('jadwal/resolve-kode', [JadwalController::class, 'resolveKode'])->name('jadwal.resolve-kode');

        Route::resource('jadwal', JadwalController::class)->except('show');
        Route::resource('arsip-akademik', ArsipAkademikController::class)->only(['index', 'store', 'destroy']);

        // ========== KEUANGAN (tagihan & pembayaran) ==========
        Route::prefix('keuangan')->name('keuangan.')->controller(KeuanganController::class)->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('generate', 'generate')->name('generate');
            Route::get('tagihan/{tagihan}', 'show')->name('tagihan.show');
            Route::delete('tagihan/{tagihan}', 'destroy')->name('tagihan.destroy');
            Route::post('tagihan/{tagihan}/pembayaran', 'bayar')->name('pembayaran.store');
            Route::delete('pembayaran/{pembayaran}', 'batalkanBayar')->name('pembayaran.destroy');
        });

        // ========== PERSURATAN ==========
        Route::resource('surat-masuk', SuratMasukController::class);
        Route::resource('surat-keluar', SuratKeluarController::class);
        Route::resource('template-surat', TemplateSuratController::class)->except('show');

        // ========== TASK MANAGEMENT ==========
        Route::resource('tasks', TaskController::class);
        Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');

        // ========== SARANA PRASARANA ==========
        Route::resource('sarana', SaranaController::class)->except('show');
        Route::get('sarana/{sarana}/peminjaman', [SaranaController::class, 'peminjaman'])->name('sarana.peminjaman');
        Route::post('sarana/{sarana}/peminjaman', [SaranaController::class, 'storePeminjaman'])->name('sarana.store-peminjaman');
        Route::put('peminjaman/{peminjaman}/kembali', [SaranaController::class, 'kembalikan'])->name('sarana.kembalikan');
        Route::get('sarana/{sarana}/pemeliharaan', [SaranaController::class, 'pemeliharaan'])->name('sarana.pemeliharaan');
        Route::post('sarana/{sarana}/pemeliharaan', [SaranaController::class, 'storePemeliharaan'])->name('sarana.store-pemeliharaan');

        // Kategori Sarana
        Route::resource('kategori-sarana', KategoriSaranaController::class)->except('show');

        // ========== USER MANAGEMENT (khusus admin) ==========
        Route::middleware('role:admin')->group(function () {
            Route::resource('users', UserController::class)->except('show');
            Route::post('users/{user}/siswa', [UserSiswaController::class, 'store'])->name('users.siswa.store');
            Route::delete('users/{user}/siswa/{siswa}', [UserSiswaController::class, 'destroy'])->withTrashed()->name('users.siswa.destroy');
            Route::get('audit-log', [AuditLogController::class, 'index'])->name('audit-log.index');

            // Pengaturan identitas & tampilan madrasah
            Route::get('pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
            Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
            Route::post('pengaturan/raport-rilis', [PengaturanController::class, 'raportRilis'])->name('pengaturan.raport-rilis');
        });

        // Siswa
        Route::get('buku-induk/{siswa}/export-pdf', [BukuIndukController::class, 'exportPdf'])->name('buku-induk.export-pdf');
        // Pencarian siswa (JSON, maks. 10 hasil). Sengaja bukan di bawah /siswa agar tidak bentrok dengan siswa/{siswa}.
        Route::get('cari/siswa', [SiswaController::class, 'cari'])->name('siswa.cari');
        Route::resource('siswa', SiswaController::class);
        Route::resource('buku-induk', BukuIndukController::class)->parameters(['buku-induk' => 'siswa']);

        // ========== IMPOR DATA (Excel/CSV) ==========
        Route::get('impor/{jenis}', [ImporController::class, 'index'])->name('impor.index');
        Route::get('impor/{jenis}/template', [ImporController::class, 'template'])->name('impor.template');
        Route::post('impor/{jenis}', [ImporController::class, 'proses'])->name('impor.proses');

        // ========== EKSPOR EXCEL ==========
        Route::prefix('ekspor')->name('ekspor.')->controller(EksporController::class)->group(function () {
            Route::get('siswa', 'siswa')->name('siswa');
            Route::get('emis', 'emis')->name('emis');
            Route::get('guru', 'guru')->name('guru');
            Route::get('absensi-siswa', 'absensiSiswa')->name('absensi-siswa');
            Route::get('nilai', 'nilai')->name('nilai');
        });

        // ========== KENAIKAN KELAS & KELULUSAN ==========
        Route::get('kenaikan-kelas', [KenaikanKelasController::class, 'index'])->name('kenaikan-kelas.index');
        Route::post('kenaikan-kelas', [KenaikanKelasController::class, 'store'])->name('kenaikan-kelas.store');
        Route::delete('kenaikan-kelas/{riwayat}', [KenaikanKelasController::class, 'batal'])->name('kenaikan-kelas.batal');

        // ========== RAPORT / ARSIP NILAI ==========
        Route::get('raport', [RaportController::class, 'index'])->name('raport.index');
        Route::get('raport/export-kelas', [RaportController::class, 'exportKelas'])->name('raport.export-kelas');
        Route::get('raport/{siswa}/manage', [RaportController::class, 'manage'])->name('raport.manage');
        Route::get('raport/{siswa}/export-pdf', [RaportController::class, 'exportPdf'])->name('raport.export-pdf');
        Route::post('raport/{siswa}/store', [RaportController::class, 'store'])->name('raport.store');
        Route::post('raport/{siswa}/pelengkap', [RaportController::class, 'storePelengkap'])->name('raport.pelengkap');
    });
});
