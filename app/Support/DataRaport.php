<?php

namespace App\Support;

use App\Models\AbsensiSiswa;
use App\Models\Mapel;
use App\Models\RaportCatatan;
use App\Models\RaportEkskul;
use App\Models\RaportKehadiran;
use App\Models\RaportNilai;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Carbon\Carbon;

/** Satu sumber data isi raport satu siswa, dipakai halaman kelola, PDF, dan cetak massal. */
class DataRaport
{
    /**
     * Rentang tanggal semester dari kode tahun pelajaran "2025/2026":
     * ganjil = Juli-Desember 2025, genap = Januari-Juni 2026. Null bila kode tidak dikenali.
     *
     * @return array{0: Carbon, 1: Carbon}|null
     */
    public static function rentangSemester(?TahunPelajaran $tahun, int $semester): ?array
    {
        if (! $tahun || ! preg_match('/^(\d{4})\/(\d{4})$/', $tahun->kode, $m)) {
            return null;
        }

        return $semester === 1
            ? [Carbon::create((int) $m[1], 7, 1)->startOfDay(), Carbon::create((int) $m[1], 12, 31)->endOfDay()]
            : [Carbon::create((int) $m[2], 1, 1)->startOfDay(), Carbon::create((int) $m[2], 6, 30)->endOfDay()];
    }

    /** @return array{sakit: int, ijin: int, tanpa_keterangan: int} */
    public static function hitungKehadiran(Siswa $siswa, ?TahunPelajaran $tahun, int $semester): array
    {
        $hasil = ['sakit' => 0, 'ijin' => 0, 'tanpa_keterangan' => 0];
        $rentang = self::rentangSemester($tahun, $semester);
        if (! $rentang) {
            return $hasil;
        }

        // Batas berupa string tanggal murni: objek Carbon terikat sebagai ISO-UTC dan menggeser batas 7 jam.
        $jumlah = AbsensiSiswa::where('siswa_id', $siswa->id)
            ->whereDate('tanggal', '>=', $rentang[0]->toDateString())
            ->whereDate('tanggal', '<=', $rentang[1]->toDateString())
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return [
            'sakit' => (int) ($jumlah['sakit'] ?? 0),
            'ijin' => (int) ($jumlah['izin'] ?? 0),
            'tanpa_keterangan' => (int) ($jumlah['alpha'] ?? 0),
        ];
    }

    public static function untuk(Siswa $siswa, ?int $tahunId, int $semester, bool $hitungUlang = false): array
    {
        $tahunPelajaran = $tahunId ? TahunPelajaran::find($tahunId) : null;
        $siswa->loadMissing('kelas');

        $nilai = RaportNilai::where('siswa_id', $siswa->id)->where('tahun_pelajaran_id', $tahunId)
            ->where('semester', $semester)->get()->keyBy('mapel_id');
        $ekskul = RaportEkskul::where('siswa_id', $siswa->id)->where('tahun_pelajaran_id', $tahunId)
            ->where('semester', $semester)->orderBy('urut')->get();
        $catatan = RaportCatatan::where('siswa_id', $siswa->id)->where('tahun_pelajaran_id', $tahunId)
            ->where('semester', $semester)->value('catatan_wali');

        $tersimpan = $hitungUlang ? null : RaportKehadiran::where('siswa_id', $siswa->id)
            ->where('tahun_pelajaran_id', $tahunId)->where('semester', $semester)->first();

        return [
            'siswa' => $siswa,
            'tahunPelajaran' => $tahunPelajaran,
            'semester' => $semester,
            'mapels' => Mapel::all(),
            'nilai' => $nilai,
            'ekskul' => $ekskul,
            'catatan' => $catatan,
            'kehadiran' => $tersimpan
                ? ['sakit' => $tersimpan->sakit, 'ijin' => $tersimpan->ijin, 'tanpa_keterangan' => $tersimpan->tanpa_keterangan]
                : self::hitungKehadiran($siswa, $tahunPelajaran, $semester),
            'kehadiranOtomatis' => $tersimpan === null,
        ];
    }
}
