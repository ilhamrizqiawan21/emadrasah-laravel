<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\Jadwal;
use App\Models\Pembayaran;
use App\Models\Pengumuman;
use App\Models\RaportNilai;
use App\Models\RaportRilis;
use App\Models\Siswa;
use App\Models\Tagihan;
use App\Models\TahunPelajaran;
use App\Support\DataRaport;
use App\Support\RekapAbsensi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Portal untuk wali murid dan akun siswa. Semua data dibatasi ke siswa yang ditautkan admin
 * (tabel wali_siswa); siswa lain, ada atau tidak, selalu 404 agar id tidak bisa ditebak.
 */
class PortalWaliController extends Controller
{
    private const URUTAN_HARI = ['Senin' => 1, 'Selasa' => 2, 'Rabu' => 3, 'Kamis' => 4, 'Jumat' => 5, 'Sabtu' => 6, 'Minggu' => 7];

    public function index(Request $request)
    {
        $anak = $request->user()->anak()->with('kelas')->orderBy('nama_lengkap')->get();

        $jumlah = AbsensiSiswa::whereIn('siswa_id', $anak->pluck('id'))
            ->whereBetween('tanggal', [today()->startOfMonth(), today()->endOfMonth()])
            ->selectRaw('siswa_id, status, count(*) as total')
            ->groupBy('siswa_id', 'status')->get()->groupBy('siswa_id');

        $rekap = $anak->mapWithKeys(function (Siswa $s) use ($jumlah) {
            $baris = array_fill_keys(RekapAbsensi::STATUS, 0);
            foreach ($jumlah[$s->id] ?? [] as $row) {
                $baris[$row->status] = (int) $row->total;
            }

            return [$s->id => $baris];
        });

        $pengumuman = Pengumuman::aktif()->untukRole($request->user()->role)->urut()->limit(3)->get();

        return view('wali.index', compact('anak', 'rekap', 'pengumuman'));
    }

    public function show(Request $request, Siswa $siswa)
    {
        $this->pastikanMilik($request, $siswa);
        $request->validate(['bulan' => ['nullable', 'date_format:Y-m']]);

        $bulan = Carbon::createFromFormat('Y-m-d', ($request->bulan ?: now()->format('Y-m')).'-01')->startOfMonth();
        $siswa->load('kelas');

        $absensi = AbsensiSiswa::where('siswa_id', $siswa->id)
            ->whereBetween('tanggal', [$bulan->copy()->startOfMonth(), $bulan->copy()->endOfMonth()])
            ->orderBy('tanggal')->get();
        $rekap = array_fill_keys(RekapAbsensi::STATUS, 0);
        foreach ($absensi as $a) {
            $rekap[$a->status]++;
        }
        $tidakHadir = $absensi->where('status', '!=', 'hadir')->values();

        $dirilis = RaportRilis::all()->map(fn ($r) => $r->tahun_pelajaran_id.'-'.$r->semester)->flip();
        $nilai = RaportNilai::with(['mapel', 'tahunPelajaran'])->where('siswa_id', $siswa->id)->get()
            ->filter(fn ($n) => $dirilis->has($n->tahun_pelajaran_id.'-'.$n->semester))
            ->sortBy(fn ($n) => $n->mapel?->urut ?? 999)
            ->groupBy(fn ($n) => $n->tahun_pelajaran_id.'-'.$n->semester);

        $jadwal = $siswa->kelas_id
            ? Jadwal::with(['mapel', 'guru'])->where('kelas_id', $siswa->kelas_id)->where('status', 'aktif')->get()
                ->sortBy(fn ($j) => (self::URUTAN_HARI[$j->hari] ?? 9).$j->jam_mulai)->groupBy('hari')
            : collect();

        $tagihan = Tagihan::with('pembayaran')->denganTerbayar()->where('siswa_id', $siswa->id)->orderByDesc('jatuh_tempo')->orderByDesc('id')->get();

        return view('wali.show', compact('siswa', 'bulan', 'rekap', 'tidakHadir', 'nilai', 'jadwal', 'tagihan'));
    }

    public function raport(Request $request, Siswa $siswa)
    {
        $this->pastikanMilik($request, $siswa);
        $request->validate(['tahun_pelajaran_id' => 'nullable|integer', 'semester' => 'nullable|in:1,2']);

        $tahunId = $request->tahun_pelajaran_id ?? TahunPelajaran::where('is_aktif', true)->value('id');
        $semester = (int) ($request->semester ?? 1);

        abort_unless(RaportRilis::dirilis($tahunId ? (int) $tahunId : null, $semester), 404, 'Raport semester ini belum dirilis.');

        $adaNilai = RaportNilai::where('siswa_id', $siswa->id)->where('tahun_pelajaran_id', $tahunId)->where('semester', $semester)->exists();
        abort_unless($adaNilai, 404, 'Raport semester ini belum tersedia.');

        $data = DataRaport::untuk($siswa, $tahunId, $semester);
        $tpKode = str_replace(['/', '\\'], '-', $data['tahunPelajaran']?->kode ?? (string) $tahunId);

        return Pdf::loadView('raport.pdf', $data)->setPaper('a4', 'portrait')
            ->stream('Raport_'.$siswa->nis.'_'.$tpKode.'_S'.$semester.'.pdf');
    }

    public function kuitansi(Request $request, Siswa $siswa, Pembayaran $pembayaran)
    {
        $this->pastikanMilik($request, $siswa);
        abort_unless($pembayaran->tagihan()->where('siswa_id', $siswa->id)->exists(), 404);

        return KeuanganController::unduhKuitansi($pembayaran);
    }

    private function pastikanMilik(Request $request, Siswa $siswa): void
    {
        abort_unless($request->user()->anak()->whereKey($siswa->id)->exists(), 404);
    }
}
