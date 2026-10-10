<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\PerkembanganSiswa;
use App\Models\RiwayatKelas;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KenaikanKelasController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['kelas_id' => 'nullable|integer', 'tahun_pelajaran_id' => 'nullable|integer']);

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $kelas = $request->kelas_id ? $kelasList->firstWhere('id', (int) $request->kelas_id) : $kelasList->first();
        $tahunList = TahunPelajaran::orderByDesc('kode')->get();
        $tahunId = $request->tahun_pelajaran_id ?: TahunPelajaran::where('is_aktif', true)->value('id');
        $tahun = $tahunList->firstWhere('id', (int) $tahunId);

        $siswa = collect();
        $diproses = collect();
        if ($kelas && $tahun) {
            $sudah = RiwayatKelas::where('tahun_pelajaran_id', $tahun->id)->pluck('siswa_id');
            $siswa = Siswa::where('kelas_id', $kelas->id)->where('status', 'Aktif')
                ->whereNotIn('id', $sudah)->orderBy('nama_lengkap')->get();
            $diproses = RiwayatKelas::with('siswa')->where('kelas_asal_id', $kelas->id)
                ->where('tahun_pelajaran_id', $tahun->id)->get()->sortBy('siswa.nama_lengkap');
        }

        return view('kenaikan-kelas.index', compact('kelasList', 'kelas', 'tahunList', 'tahun', 'siswa', 'diproses'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_asal_id' => 'required|integer|exists:kelas,id',
            'tahun_pelajaran_id' => 'required|integer|exists:tahun_pelajaran,id',
            'aksi' => 'required|array',
            'aksi.*' => 'in:naik,tinggal,lulus,tunda',
            'kelas_tujuan_id' => 'nullable|integer|exists:kelas,id|different:kelas_asal_id',
        ]);

        $aksi = collect($request->aksi);
        if ($aksi->contains('naik') && ! $request->kelas_tujuan_id) {
            return back()->withErrors(['kelas_tujuan_id' => 'Pilih kelas tujuan untuk siswa yang naik kelas.'])->withInput();
        }

        $asal = Kelas::findOrFail($request->kelas_asal_id);
        $tujuan = $request->kelas_tujuan_id ? Kelas::find($request->kelas_tujuan_id) : null;
        $tahun = TahunPelajaran::findOrFail($request->tahun_pelajaran_id);

        // Hanya siswa aktif di kelas asal yang belum punya hasil pada tahun ini.
        $sudah = RiwayatKelas::where('tahun_pelajaran_id', $tahun->id)->pluck('siswa_id');
        $siswaList = Siswa::where('kelas_id', $asal->id)->where('status', 'Aktif')
            ->whereNotIn('id', $sudah)->whereIn('id', $aksi->keys())->get();

        $jumlah = 0;
        DB::transaction(function () use ($siswaList, $aksi, $asal, $tujuan, $tahun, &$jumlah) {
            foreach ($siswaList as $siswa) {
                $hasil = $aksi[$siswa->id];
                if ($hasil === 'tunda') {
                    continue;
                }

                RiwayatKelas::create([
                    'siswa_id' => $siswa->id,
                    'tahun_pelajaran_id' => $tahun->id,
                    'hasil' => $hasil,
                    'kelas_asal_id' => $asal->id,
                    'kelas_tujuan_id' => $hasil === 'naik' ? $tujuan->id : null,
                    'kelas_asal_nama' => $asal->nama_kelas,
                    'kelas_tujuan_nama' => $hasil === 'naik' ? $tujuan->nama_kelas : null,
                    'dicatat_oleh' => auth()->id(),
                ]);

                if ($hasil === 'naik') {
                    $siswa->update(['kelas_id' => $tujuan->id]);
                } elseif ($hasil === 'lulus') {
                    $siswa->update(['status' => 'Lulus']);
                    PerkembanganSiswa::updateOrCreate(
                        ['siswa_id' => $siswa->id],
                        ['jenis_keluar' => 'Lulus', 'thn_lulus' => (int) substr($tahun->kode, -4)]
                    );
                }
                $jumlah++;
            }
        });

        return redirect()
            ->route('kenaikan-kelas.index', ['kelas_id' => $asal->id, 'tahun_pelajaran_id' => $tahun->id])
            ->with('success', "{$jumlah} siswa berhasil diproses.");
    }

    public function batal(RiwayatKelas $riwayat)
    {
        $siswa = Siswa::find($riwayat->siswa_id);

        // Hanya bila siswa masih persis di keadaan hasil proses; jika sudah diubah manual, jangan menimpa.
        $masihSama = $siswa && match ($riwayat->hasil) {
            'naik' => $siswa->kelas_id === $riwayat->kelas_tujuan_id,
            'tinggal' => $siswa->kelas_id === $riwayat->kelas_asal_id,
            'lulus' => $siswa->status === 'Lulus',
        };
        if (! $masihSama) {
            return back()->with('error', 'Tidak dapat dibatalkan: data siswa sudah diubah setelah diproses. Ubah manual lewat menu Siswa.');
        }

        DB::transaction(function () use ($riwayat, $siswa) {
            if ($riwayat->hasil === 'naik') {
                $siswa->update(['kelas_id' => $riwayat->kelas_asal_id]);
            } elseif ($riwayat->hasil === 'lulus') {
                $siswa->update(['status' => 'Aktif']);
                PerkembanganSiswa::where('siswa_id', $siswa->id)->update(['jenis_keluar' => null, 'thn_lulus' => null]);
            }
            $riwayat->delete();
        });

        return back()->with('success', 'Proses siswa dibatalkan.');
    }
}
