<?php

namespace App\Support;

use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Support\Facades\Storage;

/** Data satu kartu pelajar, siap dirender ke PDF (foto dan barcode sebagai data URI). */
class KartuPelajar
{
    /** Foto lebih besar dari ini tidak disematkan agar PDF satu kelas tidak membengkak. */
    private const BATAS_FOTO_BYTE = 2 * 1024 * 1024;

    /** @return array<string, ?string> */
    public static function data(Siswa $siswa, ?string $tahunAktif = null): array
    {
        return [
            'nama' => $siswa->nama_lengkap,
            'nis' => (string) $siswa->nis,
            'nisn' => $siswa->nisn,
            'ttl' => trim(($siswa->tempat_lahir ?: '-').', '.($siswa->tanggal_lahir?->translatedFormat('d F Y') ?? '-')),
            'kelas' => $siswa->kelas?->nama_kelas,
            'tahun' => $siswa->tahunPelajaran?->nama ?? $tahunAktif ?? TahunPelajaran::where('is_aktif', true)->value('nama'),
            'foto' => self::foto($siswa->foto),
            'barcode' => Code39::dataUri((string) $siswa->nis),
        ];
    }

    /** Hanya berkas di folder foto siswa, bertipe JPEG/PNG, dan tidak terlalu besar. */
    private static function foto(?string $path): ?string
    {
        $disk = Storage::disk('local');

        if (! $path || str_contains($path, '..') || ! str_starts_with($path, 'siswa-foto/') || ! $disk->exists($path)) {
            return null;
        }

        $mime = $disk->mimeType($path);
        if (! in_array($mime, ['image/jpeg', 'image/png'], true) || $disk->size($path) > self::BATAS_FOTO_BYTE) {
            return null;
        }

        return "data:{$mime};base64,".base64_encode($disk->get($path));
    }
}
