<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\RaportNilai;
use App\Models\Siswa;
use App\Support\Ekspor\Xlsx;
use App\Support\Impor\ImporGuru;
use App\Support\Impor\ImporSiswa;
use App\Support\RekapAbsensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EksporController extends Controller
{
    public function siswa(Request $request)
    {
        $request->validate(['kelas_id' => 'nullable|integer|exists:kelas,id', 'status' => 'nullable|in:Aktif,Lulus,Pindah,Keluar']);

        $siswa = Siswa::with('kelas')
            ->when($request->kelas_id, fn ($q) => $q->where('kelas_id', $request->kelas_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderBy('nama_lengkap')->lazy(500);

        $rows = $siswa->map(fn (Siswa $s) => [
            (string) $s->nis, $s->nisn, $s->nik, $s->nama_lengkap, $s->kelas?->nama_kelas, $s->jenis_kelamin,
            $s->tempat_lahir, $s->tanggal_lahir?->toDateString(), $s->agama, $s->alamat, $s->nama_orang_tua, $s->hp, $s->status,
        ]);

        return Xlsx::unduh('siswa.xlsx', array_column((new ImporSiswa)->kolom(), 'label'), $rows, 'Siswa');
    }

    public function guru()
    {
        $rows = Guru::orderByRaw('LENGTH(kode), kode')->lazy(500)->map(fn (Guru $g) => [
            $g->kode, $g->nama, $g->nip, $g->bidang_studi, $g->email, $g->phone, $g->beban_jp,
        ]);

        return Xlsx::unduh('guru.xlsx', array_column((new ImporGuru)->kolom(), 'label'), $rows, 'Guru');
    }

    public function absensiSiswa(Request $request)
    {
        $request->validate(['kelas_id' => 'required|integer|exists:kelas,id', 'bulan' => ['nullable', 'date_format:Y-m']]);

        $kelas = Kelas::findOrFail($request->kelas_id);
        $bulan = Carbon::createFromFormat('!Y-m-d', ($request->bulan ?: now()->format('Y-m')).'-01');
        ['siswa' => $siswa, 'rekap' => $rekap] = RekapAbsensi::untuk($kelas, $bulan);

        $rows = $siswa->values()->map(fn (Siswa $s, int $i) => [
            $i + 1, (string) $s->nis, $s->nama_lengkap,
            $rekap[$s->id]['hadir'], $rekap[$s->id]['izin'], $rekap[$s->id]['sakit'], $rekap[$s->id]['alpha'], $rekap[$s->id]['persen'],
        ]);

        return Xlsx::unduh(
            'absensi-'.Str::slug($kelas->nama_kelas).'-'.$bulan->format('Y-m').'.xlsx',
            ['No', 'NIS', 'Nama Siswa', 'Hadir', 'Izin', 'Sakit', 'Alpha', 'Kehadiran (%)'],
            $rows,
            'Absensi'
        );
    }

    public function nilai(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
            'tahun_pelajaran_id' => 'required|integer|exists:tahun_pelajaran,id',
            'semester' => 'required|in:1,2',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);
        $siswa = Siswa::where('kelas_id', $kelas->id)->where('status', 'Aktif')->orderBy('nama_lengkap')->get();
        $nilai = RaportNilai::whereIn('siswa_id', $siswa->pluck('id'))->where('tahun_pelajaran_id', $request->tahun_pelajaran_id)
            ->where('semester', $request->semester)->whereNotNull('nilai_akhir')->get()->groupBy('siswa_id');

        // Hanya mapel yang sudah ada nilainya, supaya tabel tidak penuh kolom kosong.
        $mapel = Mapel::whereIn('id', $nilai->flatten()->pluck('mapel_id')->unique())->orderBy('nama_mapel')->get();

        $rows = $siswa->values()->map(function (Siswa $s, int $i) use ($nilai, $mapel) {
            $milik = ($nilai[$s->id] ?? collect())->keyBy('mapel_id');
            $angka = $mapel->map(fn ($m) => $milik[$m->id]->nilai_akhir ?? null);
            $terisi = $angka->filter(fn ($n) => $n !== null);

            return [$i + 1, (string) $s->nis, $s->nama_lengkap, ...$angka->all(), $terisi->isEmpty() ? null : round($terisi->avg(), 1)];
        });

        return Xlsx::unduh(
            'nilai-'.Str::slug($kelas->nama_kelas).'-S'.$request->semester.'.xlsx',
            ['No', 'NIS', 'Nama Siswa', ...$mapel->pluck('nama_mapel')->all(), 'Rata-rata'],
            $rows,
            'Nilai'
        );
    }
}
