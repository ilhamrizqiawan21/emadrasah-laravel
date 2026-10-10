<?php

namespace App\Http\Controllers;

use App\Support\Impor\ImporGagal;
use App\Support\Impor\ImporGuru;
use App\Support\Impor\ImporSiswa;
use App\Support\Impor\LembarImpor;
use App\Support\Impor\Pengimpor;
use App\Support\Impor\TemplateImpor;
use Illuminate\Http\Request;

class ImporController extends Controller
{
    private const JENIS = ['siswa' => 'Siswa', 'guru' => 'Guru'];

    private function pengimpor(string $jenis): Pengimpor
    {
        return match ($jenis) {
            'siswa' => new ImporSiswa,
            'guru' => new ImporGuru,
            default => abort(404),
        };
    }

    public function index(string $jenis)
    {
        $pengimpor = $this->pengimpor($jenis);
        $label = self::JENIS[$jenis];
        $kolom = $pengimpor->kolom();
        $hasil = session('hasil_impor');

        return view('impor.index', compact('jenis', 'label', 'kolom', 'hasil'));
    }

    public function template(string $jenis)
    {
        $path = TemplateImpor::buat($this->pengimpor($jenis));

        return response()->download($path, "template-{$jenis}.xlsx", [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    public function proses(Request $request, string $jenis)
    {
        $pengimpor = $this->pengimpor($jenis);

        $request->validate([
            'berkas' => 'required|file|max:2048|mimes:xlsx,csv,txt|extensions:xlsx,csv',
        ], [
            'berkas.required' => 'Pilih berkas xlsx atau csv terlebih dulu.',
            'berkas.max' => 'Ukuran berkas maksimal 2 MB.',
            'berkas.mimes' => 'Berkas harus berformat xlsx atau csv.',
            'berkas.extensions' => 'Berkas harus berformat xlsx atau csv.',
        ]);

        $berkas = $request->file('berkas');
        $periksa = $request->boolean('periksa');

        try {
            $rows = LembarImpor::baca($berkas->getRealPath(), strtolower($berkas->getClientOriginalExtension()), $pengimpor->kolom());
        } catch (ImporGagal $e) {
            return back()->withErrors(['berkas' => $e->getMessage()]);
        }

        $hasil = $pengimpor->proses($rows, $periksa);
        $total = $hasil['diimpor'] + count($hasil['dilewati']);

        $redirect = redirect()->route('impor.index', $jenis)->with('hasil_impor', $hasil);

        return $periksa ? $redirect : $redirect->with('success', "{$hasil['diimpor']} dari {$total} baris berhasil diimpor.");
    }
}
