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

    /** Data siswa selengkap mungkin (identitas, alamat, orang tua/wali) untuk bahan entri Dapodik/EMIS. */
    public function emis(Request $request)
    {
        $request->validate(['kelas_id' => 'nullable|integer|exists:kelas,id', 'status' => 'nullable|in:Aktif,Lulus,Pindah,Keluar']);

        $kolom = [
            'NIS' => fn (Siswa $s) => (string) $s->nis, 'NISN' => fn (Siswa $s) => $s->nisn, 'NISM' => fn (Siswa $s) => $s->nism,
            'NIK' => fn (Siswa $s) => $s->nik, 'No. KK' => fn (Siswa $s) => $s->no_kk,
            'Nama Lengkap' => fn (Siswa $s) => $s->nama_lengkap, 'L/P' => fn (Siswa $s) => $s->jenis_kelamin,
            'Tempat Lahir' => fn (Siswa $s) => $s->tempat_lahir, 'Tanggal Lahir' => fn (Siswa $s) => $s->tanggal_lahir?->toDateString(),
            'Agama' => fn (Siswa $s) => $s->agama, 'Kewarganegaraan' => fn (Siswa $s) => $s->kewarganegaraan,
            'Anak Ke' => fn (Siswa $s) => $s->anak_ke, 'Jumlah Saudara Kandung' => fn (Siswa $s) => $s->saudara_kandung,
            'Alamat' => fn (Siswa $s) => $s->alamat, 'RT' => fn (Siswa $s) => $s->rt, 'RW' => fn (Siswa $s) => $s->rw,
            'Desa/Kelurahan' => fn (Siswa $s) => $s->desa_kelurahan, 'Kecamatan' => fn (Siswa $s) => $s->kecamatan,
            'Kabupaten/Kota' => fn (Siswa $s) => $s->kabupaten_kota, 'Provinsi' => fn (Siswa $s) => $s->provinsi, 'Kode Pos' => fn (Siswa $s) => $s->kode_pos,
            'Tempat Tinggal' => fn (Siswa $s) => $s->bertempat_tinggal_pada, 'Moda Transportasi' => fn (Siswa $s) => $s->moda_transportasi,
            'Jarak ke Madrasah' => fn (Siswa $s) => $s->jarak_ke_madrasah,
            'Tinggi Badan' => fn (Siswa $s) => $s->tinggi_badan_awal, 'Berat Badan' => fn (Siswa $s) => $s->berat_badan_awal,
            'No. HP' => fn (Siswa $s) => $s->hp, 'Kelas' => fn (Siswa $s) => $s->kelas?->nama_kelas, 'Tingkat' => fn (Siswa $s) => $s->kelas?->tingkat,
            'Status' => fn (Siswa $s) => $s->status,
        ];
        foreach (['ayah' => 'Ayah', 'ibu' => 'Ibu', 'wali' => 'Wali'] as $kunci => $label) {
            $kolom["Nama $label"] = fn (Siswa $s) => $s->orangTuaWali?->{"nama_$kunci"};
            $kolom["Pendidikan $label"] = fn (Siswa $s) => $s->orangTuaWali?->{"pendidikan_$kunci"};
            $kolom["Pekerjaan $label"] = fn (Siswa $s) => $s->orangTuaWali?->{"pekerjaan_$kunci"};
            if ($kunci !== 'wali') {
                $kolom["Penghasilan $label"] = fn (Siswa $s) => $s->orangTuaWali?->{"penghasilan_$kunci"};
            }
            $kolom["No. HP $label"] = fn (Siswa $s) => $s->orangTuaWali?->{"no_hp_$kunci"};
        }

        $rows = Siswa::with(['kelas', 'orangTuaWali'])
            ->where('status', $request->input('status', 'Aktif'))
            ->when($request->kelas_id, fn ($q) => $q->where('kelas_id', $request->kelas_id))
            ->orderBy('nama_lengkap')->lazy(500)
            ->map(fn (Siswa $s) => array_map(fn ($ambil) => $ambil($s), array_values($kolom)));

        return Xlsx::unduh('siswa-emis.xlsx', array_keys($kolom), $rows, 'Siswa');
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
