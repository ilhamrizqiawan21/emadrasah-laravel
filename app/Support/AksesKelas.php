<?php

namespace App\Support;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Satu-satunya tempat aturan "kelas mana yang boleh dikelola siapa".
 * Admin/operator: semua. Guru: kelas yang dia walikan atau ajar (jadwal); untuk nilai,
 * hanya kelas dan mapel yang ada di jadwalnya. Guru tanpa data guru terkait → 403.
 */
class AksesKelas
{
    /** ID data guru milik user guru; abort 403 bila akun belum terhubung. Null untuk non-guru. */
    public static function guruId(User $user): ?int
    {
        if ($user->role !== 'guru') {
            return null;
        }

        $id = Guru::where('user_id', $user->id)->value('id');
        abort_if($id === null, 403, 'Akun Anda belum terhubung dengan data guru.');

        return (int) $id;
    }

    /** Kelas yang walikelasnya atau diajar user (untuk absensi dan portal). */
    public static function kelas(User $user): Collection
    {
        $query = Kelas::orderBy('nama_kelas');
        $guruId = self::guruId($user);

        return $guruId === null ? $query->get() : $query->where(function ($q) use ($guruId) {
            $q->where('guru_pembimbing_id', $guruId)
                ->orWhereHas('jadwals', fn ($j) => $j->where('guru_id', $guruId));
        })->get();
    }

    /** Kelas yang diajar user (untuk input nilai); wali kelas saja tidak cukup. */
    public static function kelasMengajar(User $user): Collection
    {
        $query = Kelas::orderBy('nama_kelas');
        $guruId = self::guruId($user);

        return $guruId === null ? $query->get()
            : $query->whereHas('jadwals', fn ($j) => $j->where('guru_id', $guruId))->get();
    }

    /** Mapel yang boleh dinilai user di kelas tertentu. */
    public static function mapel(User $user, int $kelasId): Collection
    {
        $query = Mapel::orderBy('nama_mapel');
        $guruId = self::guruId($user);

        return $guruId === null ? $query->get()
            : $query->whereHas('jadwals', fn ($j) => $j->where('guru_id', $guruId)->where('kelas_id', $kelasId))->get();
    }
}
