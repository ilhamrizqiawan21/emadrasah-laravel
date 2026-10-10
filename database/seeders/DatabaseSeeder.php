<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    /** Data contoh/awal tidak dicatat di audit log: log hanya untuk tindakan pengguna. */
    public function run(): void
    {
        AuditLog::tanpaAudit(fn () => $this->isi());
    }

    private function isi(): void
    {
        // Akun demo hanya untuk pengembangan/demo. Di produksi admin pertama dibuat dengan
        // `php artisan madrasah:install` (kata sandi sendiri), bukan kredensial yang diketahui umum.
        if (! app()->isProduction()) {
            User::firstOrCreate(
                ['email' => 'admin@madrasah.id'],
                [
                    'name' => 'Administrator',
                    'password' => Hash::make('admin123'),
                    'role' => 'admin',
                    'is_active' => true,
                ]
            );
        }

        // Kategori Sarana
        $kategoriList = ['Elektronik', 'Furniture', 'Alat Peraga', 'Olahraga'];
        foreach ($kategoriList as $nama) {
            DB::table('kategori_sarana')->updateOrInsert(
                ['nama_kategori' => $nama],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }

        // Tahun Pelajaran Default Aktif
        DB::table('tahun_pelajaran')->updateOrInsert(
            ['kode' => '2025/2026'],
            [
                'nama' => 'Tahun Ajaran 2025/2026',
                'is_aktif' => true,
                'created_at' => now(),
            ]
        );

        // Data contoh (siswa, guru, surat, akun demo) tidak boleh ikut ke produksi.
        if (! app()->isProduction()) {
            $this->call([
                SaranaSeeder::class,
                DummyDataSeeder::class,
            ]);
        }
    }
}
