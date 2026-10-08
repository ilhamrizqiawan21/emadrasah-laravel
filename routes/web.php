<?php

use App\Http\Controllers\AgendaGuruController;
use App\Http\Controllers\ArsipAkademikController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BrandingController;
use App\Http\Controllers\BukuIndukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JamPelajaranController;
use App\Http\Controllers\KategoriSaranaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\RaportController;
use App\Http\Controllers\SaranaController;
use App\Http\Controllers\SiswaController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\TahunPelajaranController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TemplateSuratController;
use App\Http\Controllers\UserController;
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

// Logo & favicon madrasah: publik karena dipakai halaman login dan tab browser.
Route::get('/branding/{type}', [BrandingController::class, 'show'])
    ->whereIn('type', ['logo', 'favicon'])
    ->name('branding.show');

// ==================== PROTECTED ROUTES (WAJIB LOGIN) ====================
Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

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

    // ========== MENU MANAJEMEN (khusus admin & operator) ==========
    Route::middleware('role:admin,operator')->group(function () {

        // ========== BERKAS UPLOAD (privat, wajib login) ==========
        Route::get('files/{path}', [FileController::class, 'show'])->where('path', '.*')->name('files.show');

        // ========== MASTER DATA ==========
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

            // Pengaturan identitas & tampilan madrasah
            Route::get('pengaturan', [PengaturanController::class, 'edit'])->name('pengaturan.edit');
            Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
        });

        // Siswa
        Route::get('buku-induk/{siswa}/export-pdf', [BukuIndukController::class, 'exportPdf'])->name('buku-induk.export-pdf');
        Route::resource('siswa', SiswaController::class);
        Route::resource('buku-induk', BukuIndukController::class)->parameters(['buku-induk' => 'siswa']);

        // ========== RAPORT / ARSIP NILAI ==========
        Route::get('raport', [RaportController::class, 'index'])->name('raport.index');
        Route::get('raport/{siswa}/manage', [RaportController::class, 'manage'])->name('raport.manage');
        Route::get('raport/{siswa}/export-pdf', [RaportController::class, 'exportPdf'])->name('raport.export-pdf');
        Route::post('raport/{siswa}/store', [RaportController::class, 'store'])->name('raport.store');
    });
});
