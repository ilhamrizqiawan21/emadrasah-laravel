<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Str;

/**
 * Mencatat dibuat/diubah/dihapus/dipulihkan ke audit_logs. Hanya berlaku untuk operasi lewat model
 * (create/save/update/delete); `Model::query()->update()` massal tidak memicu event dan tidak tercatat.
 */
trait Diaudit
{
    /** Kolom yang nilainya tidak boleh tersimpan di log; hanya ditandai "[diubah]". Timpa di model bila perlu. */
    protected function auditRahasia(): array
    {
        return ['password'];
    }

    /** Kolom yang diabaikan sama sekali. */
    protected array $auditKecualikan = [];

    public static function bootDiaudit(): void
    {
        static::created(fn ($m) => AuditLog::catat($m, 'dibuat', ['baru' => $m->auditNilai($m->getAttributes())]));

        static::updated(function ($m) {
            $baru = $m->auditNilai($m->getChanges());
            if ($baru === []) {
                return; // hanya updated_at yang berubah
            }
            // Kolom yang belum pernah dimuat/diisi dianggap kosong (null) sebelum diubah.
            $lama = $m->auditNilai(array_combine(array_keys($baru), array_map(fn ($k) => $m->getRawOriginal($k), array_keys($baru))));
            AuditLog::catat($m, 'diubah', ['lama' => $lama, 'baru' => $baru]);
        });

        static::deleted(fn ($m) => AuditLog::catat($m, 'dihapus', ['lama' => $m->auditNilai($m->getAttributes())]));

        if (method_exists(static::class, 'bootSoftDeletes')) {
            static::restored(fn ($m) => AuditLog::catat($m, 'dipulihkan', null));
        }
    }

    /** Teks pengenal yang mudah dibaca manusia untuk baris ini; ditimpa per model. */
    public function auditLabel(): string
    {
        return class_basename($this).' #'.$this->getKey();
    }

    /** @return array<string, mixed> */
    protected function auditNilai(array $atribut): array
    {
        $buang = array_merge(['created_at', 'updated_at', 'deleted_at', 'remember_token'], $this->auditKecualikan);
        $hasil = [];

        foreach ($atribut as $kolom => $nilai) {
            if (in_array($kolom, $buang, true) || $kolom === $this->getKeyName()) {
                continue;
            }
            $hasil[$kolom] = in_array($kolom, $this->auditRahasia(), true)
                ? '[diubah]'
                : (is_string($nilai) ? Str::limit($nilai, 200, '…') : $nilai);
        }

        return $hasil;
    }
}
