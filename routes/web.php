<?php

use App\Http\Controllers\AgendaGuruController;
use App\Http\Controllers\ArsipAkademikController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BukuIndukController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\JamPelajaranController;
use App\Http\Controllers\KategoriSaranaController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
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

$resourceParameters = [
    'arsip-akademik' => 'arsip_akademik',
    'buku-induk' => 'siswa',
    'guru' => 'guru',
    'jam-pelajaran' => 'jam_pelajaran',
    'kategori-sarana' => 'kategori_sarana',
    'kelas' => 'kelas',
    'mapel' => 'mapel',
    'siswa' => 'siswa',
    'surat-keluar' => 'surat_keluar',
    'surat-masuk' => 'surat_masuk',
    'tahun-pelajaran' => 'tahun_pelajaran',
    'template-surat' => 'template_surat',
    'users' => 'user',
];

$securedResource = function (string $uri, string $controller, string $permission) use ($resourceParameters): void {
    $parameters = [$uri => $resourceParameters[$uri] ?? str_replace('-', '_', $uri)];

    Route::middleware("permission:{$permission}.create")->group(function () use ($uri, $controller, $parameters) {
        Route::resource($uri, $controller)->parameters($parameters)->only(['create', 'store']);
    });

    Route::middleware("permission:{$permission}.update")->group(function () use ($uri, $controller, $parameters) {
        Route::resource($uri, $controller)->parameters($parameters)->only(['edit', 'update']);
    });

    Route::middleware("permission:{$permission}.delete")->group(function () use ($uri, $controller, $parameters) {
        Route::resource($uri, $controller)->parameters($parameters)->only(['destroy']);
    });

    Route::middleware("permission:{$permission}.view")->group(function () use ($uri, $controller, $parameters) {
        Route::resource($uri, $controller)->parameters($parameters)->only(['index', 'show']);
    });
};

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==================== GUEST ROUTES (TIDAK PERLU LOGIN) ====================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
// Logout akan di dalam grup auth (karena butuh login dulu)

// ==================== PROTECTED ROUTES (WAJIB LOGIN) ====================
Route::middleware(['auth', 'active'])->group(function () use ($securedResource) {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard (halaman utama setelah login)
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:dashboard.view')
        ->name('dashboard');

    // ========== MASTER DATA ==========
    $securedResource('guru', GuruController::class, 'guru');
    $securedResource('kelas', KelasController::class, 'kelas');
    $securedResource('mapel', MapelController::class, 'mapel');
    $securedResource('jam-pelajaran', JamPelajaranController::class, 'jam_pelajaran');
    $securedResource('tahun-pelajaran', TahunPelajaranController::class, 'tahun_pelajaran');

    // ========== JADWAL PELAJARAN ==========
    Route::post('jadwal/grid-store', [JadwalController::class, 'gridStore'])->middleware('permission:jadwal.create')->name('jadwal.grid-store');
    Route::resource('jadwal', JadwalController::class)->only(['create', 'store'])->middleware('permission:jadwal.create');
    Route::resource('jadwal', JadwalController::class)->only(['edit', 'update'])->middleware('permission:jadwal.update');
    Route::resource('jadwal', JadwalController::class)->only(['destroy'])->middleware('permission:jadwal.delete');
    Route::middleware('permission:jadwal.view')->group(function () {
        Route::get('jadwal/grid', [JadwalController::class, 'grid'])->name('jadwal.grid');
        Route::get('jadwal/resolve-kode', [JadwalController::class, 'resolveKode'])->name('jadwal.resolve-kode');
        Route::resource('jadwal', JadwalController::class)->only(['index', 'show']);
    });

    Route::delete('arsip-akademik/bulk-destroy', [ArsipAkademikController::class, 'bulkDestroy'])
        ->middleware('permission:arsip_akademik.delete')
        ->name('arsip-akademik.bulk-destroy');
    $securedResource('arsip-akademik', ArsipAkademikController::class, 'arsip_akademik');

    // ========== ABSENSI GURU & GURU PENGGANTI ==========
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::middleware('permission:absensi.view')->group(function () {
            Route::get('/', [AgendaGuruController::class, 'index'])->name('index');
            Route::get('rekap', [AgendaGuruController::class, 'rekap'])->name('rekap');
            Route::get('pengganti/{agenda}', [AgendaGuruController::class, 'pengganti'])->name('pengganti');
        });
        Route::post('/', [AgendaGuruController::class, 'store'])->middleware('permission:absensi.create,absensi.update')->name('store');
        Route::post('pengganti/{agenda}', [AgendaGuruController::class, 'storePengganti'])->middleware('permission:absensi.create,absensi.update')->name('store-pengganti');
        Route::get('rekap/export', [AgendaGuruController::class, 'exportPdf'])->middleware('permission:absensi.export')->name('export-pdf');
    });

    // ========== PERSURATAN ==========
    Route::delete('surat-masuk/bulk-destroy', [SuratMasukController::class, 'bulkDestroy'])
        ->middleware('permission:surat_masuk.delete')
        ->name('surat-masuk.bulk-destroy');
    Route::delete('surat-keluar/bulk-destroy', [SuratKeluarController::class, 'bulkDestroy'])
        ->middleware('permission:surat_keluar.delete')
        ->name('surat-keluar.bulk-destroy');
    $securedResource('surat-masuk', SuratMasukController::class, 'surat_masuk');
    $securedResource('surat-keluar', SuratKeluarController::class, 'surat_keluar');
    $securedResource('template-surat', TemplateSuratController::class, 'template_surat');

    // ========== TASK MANAGEMENT ==========
    Route::resource('tasks', TaskController::class)->only(['create', 'store'])->middleware('permission:tasks.create');
    Route::resource('tasks', TaskController::class)->only(['edit', 'update'])->middleware('permission:tasks.update');
    Route::resource('tasks', TaskController::class)->only(['destroy'])->middleware('permission:tasks.delete');
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->middleware('permission:tasks.update')->name('tasks.status');
    Route::middleware('permission:tasks.view')->group(function () {
        Route::resource('tasks', TaskController::class)->only(['index', 'show']);
    });

    // ========== SARANA PRASARANA ==========
    // Kategori sarana (bisa dibuat CRUD terpisah, atau digabung dengan SaranaController)
    // Jika belum ada controller khusus untuk kategori, kita bisa tambahkan nanti.
    Route::resource('sarana', SaranaController::class)->only(['create', 'store'])->middleware('permission:sarana.create');
    Route::resource('sarana', SaranaController::class)->only(['edit', 'update'])->middleware('permission:sarana.update');
    Route::resource('sarana', SaranaController::class)->only(['destroy'])->middleware('permission:sarana.delete');
    Route::post('sarana/{sarana}/peminjaman', [SaranaController::class, 'storePeminjaman'])->middleware('permission:sarana.create')->name('sarana.store-peminjaman');
    Route::put('peminjaman/{peminjaman}/kembali', [SaranaController::class, 'kembalikan'])->middleware('permission:sarana.update')->name('sarana.kembalikan');
    Route::post('sarana/{sarana}/pemeliharaan', [SaranaController::class, 'storePemeliharaan'])->middleware('permission:sarana.create')->name('sarana.store-pemeliharaan');
    Route::middleware('permission:sarana.view')->group(function () {
        Route::get('sarana/{sarana}/peminjaman', [SaranaController::class, 'peminjaman'])->name('sarana.peminjaman');
        Route::get('sarana/{sarana}/pemeliharaan', [SaranaController::class, 'pemeliharaan'])->name('sarana.pemeliharaan');
        Route::resource('sarana', SaranaController::class)->only(['index', 'show']);
    });

    $securedResource('kategori-sarana', KategoriSaranaController::class, 'kategori_sarana');

    // ========== USER MANAGEMENT ==========
    $securedResource('users', UserController::class, 'users');

    // Siswa
    $securedResource('siswa', SiswaController::class, 'siswa');
    $securedResource('buku-induk', BukuIndukController::class, 'buku_induk');
    Route::get('buku-induk/{siswa}/export-pdf', [BukuIndukController::class, 'exportPdf'])
        ->middleware('permission:buku_induk.export')
        ->name('buku-induk.export-pdf');

    // ========== RAPORT / ARSIP NILAI ==========
    Route::middleware('permission:raport.view')->group(function () {
        Route::get('raport', [RaportController::class, 'index'])->name('raport.index');
        Route::get('raport/{siswa}/manage', [RaportController::class, 'manage'])->name('raport.manage');
    });
    Route::get('raport/{siswa}/export-pdf', [RaportController::class, 'exportPdf'])
        ->middleware('permission:raport.export')
        ->name('raport.export-pdf');
    Route::post('raport/{siswa}/store', [RaportController::class, 'store'])
        ->middleware('permission:raport.create,raport.update')
        ->name('raport.store');
});
