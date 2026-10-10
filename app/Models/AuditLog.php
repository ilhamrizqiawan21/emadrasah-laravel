<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** Jejak perubahan data penting: siapa, kapan, apa, nilai lama dan baru. Tidak pernah diubah dari aplikasi. */
class AuditLog extends Model
{
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'perubahan' => 'array',
        'created_at' => 'datetime',
    ];

    /** Model yang dicatat => nama yang ditampilkan di halaman audit. */
    public const JENIS = [
        Siswa::class => 'Siswa',
        Guru::class => 'Guru',
        Kelas::class => 'Kelas',
        User::class => 'Pengguna',
        RaportNilai::class => 'Nilai',
        RiwayatKelas::class => 'Riwayat Kelas',
        TahunPelajaran::class => 'Tahun Pelajaran',
        Setting::class => 'Pengaturan',
    ];

    public const AKSI = ['dibuat', 'diubah', 'dihapus', 'dipulihkan'];

    private static bool $nonaktif = false;

    /** Jalankan tanpa mencatat (mis. seeder data contoh); status semula dipulihkan setelahnya. */
    public static function tanpaAudit(callable $callback): mixed
    {
        $sebelumnya = self::$nonaktif;
        self::$nonaktif = true;
        try {
            return $callback();
        } finally {
            self::$nonaktif = $sebelumnya;
        }
    }

    public static function catat(Model $model, string $aksi, ?array $perubahan): void
    {
        if (self::$nonaktif) {
            return;
        }

        try {
            $user = auth()->user();
            static::create([
                'user_id' => $user?->getAuthIdentifier(),
                'user_nama' => $user?->name ?? 'Sistem',
                'aksi' => $aksi,
                'auditable_type' => $model::class,
                'auditable_id' => $model->getKey(),
                'label' => mb_substr((string) $model->auditLabel(), 0, 255),
                'perubahan' => $perubahan,
                'ip' => app()->runningInConsole() ? null : request()->ip(),
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Gagal mencatat tidak boleh menggagalkan pekerjaan pengguna (mis. menyimpan nilai); laporkan saja.
            report($e);
        }
    }

    public function jenis(): string
    {
        return self::JENIS[$this->auditable_type] ?? class_basename($this->auditable_type);
    }

    /** Ringkasan perubahan satu baris untuk tabel: "hp: kosong → 0812; status: Aktif → Lulus". */
    public function ringkasan(int $maks = 4): string
    {
        $baru = $this->perubahan['baru'] ?? [];
        if ($this->aksi !== 'diubah' || ! $baru) {
            return '';
        }

        $teks = fn ($v) => $v === null || $v === '' ? 'kosong' : (is_scalar($v) ? (string) $v : json_encode($v));
        $bagian = [];
        foreach (array_slice($baru, 0, $maks, true) as $kolom => $nilai) {
            $bagian[] = str_replace('_', ' ', $kolom).': '.$teks($this->perubahan['lama'][$kolom] ?? null).' → '.$teks($nilai);
        }
        if (count($baru) > $maks) {
            $bagian[] = '+'.(count($baru) - $maks).' lainnya';
        }

        return implode('; ', $bagian);
    }
}
