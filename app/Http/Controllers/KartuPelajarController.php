<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Support\KartuPelajar;
use Barryvdh\DomPDF\Facade\Pdf;

/** Cetak kartu pelajar (ukuran kartu ID-1) per siswa, atau satu lembar A4 per kelas. Hanya siswa aktif. */
class KartuPelajarController extends Controller
{
    /** Ukuran kartu ID-1 (85,6 x 54 mm) dalam poin. */
    private const UKURAN_KARTU = [0, 0, 242.65, 153.07];

    public function siswa(Siswa $siswa)
    {
        if ($siswa->status !== 'Aktif') {
            return back()->with('error', 'Kartu pelajar hanya dicetak untuk siswa berstatus Aktif.');
        }

        $siswa->load('kelas', 'tahunPelajaran');

        return Pdf::loadView('kartu-pelajar.cetak', ['kartu' => [KartuPelajar::data($siswa)], 'satu' => true])
            ->setPaper(self::UKURAN_KARTU)->stream('Kartu_Pelajar_'.$siswa->nis.'.pdf');
    }

    public function kelas(Kelas $kelas)
    {
        $siswa = Siswa::with('kelas', 'tahunPelajaran')->where('kelas_id', $kelas->id)->where('status', 'Aktif')->orderBy('nama_lengkap')->get();

        if ($siswa->isEmpty()) {
            return back()->with('error', 'Kelas ini belum punya siswa aktif.');
        }

        $tahun = TahunPelajaran::where('is_aktif', true)->value('nama');

        return Pdf::loadView('kartu-pelajar.cetak', ['kartu' => $siswa->map(fn (Siswa $s) => KartuPelajar::data($s, $tahun))->all(), 'satu' => false])
            ->setPaper('a4', 'portrait')->stream('Kartu_Pelajar_'.str_replace(' ', '-', $kelas->nama_kelas).'.pdf');
    }
}
