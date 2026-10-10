<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\SuratKeluar;
use App\Models\TahunPelajaran;
use App\Support\NomorSurat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

/** Surat keterangan siswa aktif: bernomor otomatis, tercatat di register surat keluar, dan dicetak dari salinan data saat terbit. */
class SuratSiswaController extends Controller
{
    public const JENIS = 'keterangan_aktif';

    public function index(Siswa $siswa)
    {
        $surat = SuratKeluar::where('siswa_id', $siswa->id)->where('jenis', self::JENIS)->orderByDesc('id')->get();

        return view('surat-siswa.index', ['siswa' => $siswa->load('kelas'), 'surat' => $surat]);
    }

    public function store(Request $request, Siswa $siswa)
    {
        $data = $request->validate(['keperluan' => 'required|string|max:255']);

        if ($siswa->status !== 'Aktif') {
            return back()->with('error', 'Surat keterangan aktif hanya dapat diterbitkan untuk siswa berstatus Aktif.');
        }

        $siswa->load('kelas', 'tahunPelajaran');
        $tanggal = today();
        $salinan = [
            'nama' => $siswa->nama_lengkap,
            'nis' => $siswa->nis,
            'nisn' => $siswa->nisn,
            'ttl' => trim(($siswa->tempat_lahir ?: '-').', '.($siswa->tanggal_lahir?->translatedFormat('d F Y') ?? '-')),
            'kelas' => $siswa->kelas?->nama_kelas,
            'tahun_pelajaran' => $siswa->tahunPelajaran?->nama ?? TahunPelajaran::where('is_aktif', true)->value('nama'),
        ];

        // Dua petugas yang menerbitkan bersamaan bisa menghitung nomor yang sama; kolom nomor unik, jadi ulangi dengan nomor baru.
        for ($coba = 1; ; $coba++) {
            try {
                $surat = SuratKeluar::create([
                    'nomor_surat' => NomorSurat::berikut($tanggal),
                    'siswa_id' => $siswa->id,
                    'jenis' => self::JENIS,
                    'tujuan' => 'Yang berkepentingan',
                    'perihal' => "Surat Keterangan Aktif - {$siswa->nama_lengkap}",
                    'keperluan' => $data['keperluan'],
                    'tanggal_kirim' => $tanggal,
                    'data' => $salinan,
                ]);
                break;
            } catch (UniqueConstraintViolationException $e) {
                if ($coba >= 3) {
                    throw $e;
                }
            }
        }

        return redirect()->route('surat-siswa.index', $siswa)->with('success', "Surat nomor {$surat->nomor_surat} diterbitkan.");
    }

    public function cetak(SuratKeluar $surat)
    {
        abort_unless($surat->siswa_id !== null && $surat->jenis === self::JENIS, 404);

        return Pdf::loadView('surat-siswa.keterangan-aktif', compact('surat'))->setPaper('a4', 'portrait')
            ->stream('Surat_Keterangan_Aktif_'.str_replace('/', '-', $surat->nomor_surat).'.pdf');
    }
}
