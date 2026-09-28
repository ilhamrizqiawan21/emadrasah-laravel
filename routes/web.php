<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\MapelController;
use App\Http\Controllers\JamPelajaranController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AgendaGuruController;
use App\Http\Controllers\SuratMasukController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\TemplateSuratController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\SaranaController;
use App\Http\Controllers\KategoriSaranaController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BukuIndukController;
use App\Http\Controllers\ArsipAkademikController;
use App\Http\Controllers\RaportController;
use App\Http\Controllers\TahunPelajaranController;
use App\Http\Controllers\SiswaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ==================== GUEST ROUTES (TIDAK PERLU LOGIN) ====================
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');
// Logout akan di dalam grup auth (karena butuh login dulu)


// ==================== PROTECTED ROUTES (WAJIB LOGIN) ====================
Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Dashboard (halaman utama setelah login)
    Route::get('/', fn() => redirect()->route('dashboard'));
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ========== MASTER DATA ==========
    Route::resource('guru', GuruController::class);
    Route::resource('kelas', KelasController::class);
    Route::resource('mapel', MapelController::class);
    Route::resource('jam-pelajaran', JamPelajaranController::class);
    Route::resource('tahun-pelajaran', TahunPelajaranController::class);

    // ========== JADWAL PELAJARAN ==========
    Route::get('jadwal/grid', [JadwalController::class, 'grid'])->name('jadwal.grid');
Route::post('jadwal/grid-store', [JadwalController::class, 'gridStore'])->name('jadwal.grid-store');
Route::get('jadwal/resolve-kode', [JadwalController::class, 'resolveKode'])->name('jadwal.resolve-kode');

Route::resource('jadwal', JadwalController::class);
Route::resource('arsip-akademik', ArsipAkademikController::class);
    // ========== ABSENSI GURU & GURU PENGGANTI ==========
    Route::prefix('absensi')->name('absensi.')->group(function () {
        Route::get('/', [AgendaGuruController::class, 'index'])->name('index');
        Route::post('/', [AgendaGuruController::class, 'store'])->name('store');
        Route::get('rekap', [AgendaGuruController::class, 'rekap'])->name('rekap');
        Route::get('rekap/export', [AgendaGuruController::class, 'exportPdf'])->name('export-pdf');
        Route::get('pengganti/{agenda}', [AgendaGuruController::class, 'pengganti'])->name('pengganti');
        Route::post('pengganti/{agenda}', [AgendaGuruController::class, 'storePengganti'])->name('store-pengganti');
    });

    // ========== PERSURATAN ==========
    Route::resource('surat-masuk', SuratMasukController::class);
    Route::resource('surat-keluar', SuratKeluarController::class);
    Route::resource('template-surat', TemplateSuratController::class);

    // ========== TASK MANAGEMENT ==========
    Route::resource('tasks', TaskController::class);
    Route::patch('tasks/{task}/status', [TaskController::class, 'updateStatus'])->name('tasks.status');

    // ========== SARANA PRASARANA ==========
    // Kategori sarana (bisa dibuat CRUD terpisah, atau digabung dengan SaranaController)
    // Jika belum ada controller khusus untuk kategori, kita bisa tambahkan nanti.
    Route::resource('sarana', SaranaController::class);
    Route::get('sarana/{sarana}/peminjaman', [SaranaController::class, 'peminjaman'])->name('sarana.peminjaman');
    Route::post('sarana/{sarana}/peminjaman', [SaranaController::class, 'storePeminjaman'])->name('sarana.store-peminjaman');
    Route::put('peminjaman/{peminjaman}/kembali', [SaranaController::class, 'kembalikan'])->name('sarana.kembalikan');
    // Pemeliharaan sarana (jika dibuat controller terpisah, sesuaikan)
    Route::get('sarana/{sarana}/pemeliharaan', [SaranaController::class, 'pemeliharaan'])->name('sarana.pemeliharaan');
    Route::post('sarana/{sarana}/pemeliharaan', [SaranaController::class, 'storePemeliharaan'])->name('sarana.store-pemeliharaan');

    //Kategori Sarana
    Route::resource('kategori-sarana', KategoriSaranaController::class);

    // ========== USER MANAGEMENT (khusus admin) ==========
    Route::middleware('role:admin')->group(function () {
        Route::resource('users', UserController::class);
    });

    //Siswa
    Route::get('buku-induk/{siswa}/export-pdf', [BukuIndukController::class, 'exportPdf'])->name('buku-induk.export-pdf');
    Route::resource('siswa', SiswaController::class);
    Route::resource('buku-induk', BukuIndukController::class);

    // ========== RAPORT / ARSIP NILAI ==========
    Route::get('raport', [RaportController::class, 'index'])->name('raport.index');
    Route::get('raport/{siswa}/manage', [RaportController::class, 'manage'])->name('raport.manage');
    Route::get('raport/{siswa}/export-pdf', [RaportController::class, 'exportPdf'])->name('raport.export-pdf');
    Route::post('raport/{siswa}/store', [RaportController::class, 'store'])->name('raport.store');
});