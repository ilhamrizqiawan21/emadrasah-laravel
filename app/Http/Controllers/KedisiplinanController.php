<?php

namespace App\Http\Controllers;

use App\Models\CatatanBk;
use App\Models\Kelas;
use App\Models\Pelanggaran;
use App\Models\PelanggaranJenis;
use App\Models\Siswa;
use Illuminate\Http\Request;

/** Pelanggaran berpoin dan catatan konseling BK. Hanya admin/operator; isi BK tidak tampil di portal wali. */
class KedisiplinanController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'kelas_id' => 'nullable|integer|exists:kelas,id',
            'search' => 'nullable|string|max:100',
            'bermasalah' => 'nullable|boolean',
        ]);

        $daftar = Siswa::with('kelas')->where('status', 'Aktif')
            ->withSum('pelanggaran as total_poin', 'poin')
            ->when($request->kelas_id, fn ($q, $v) => $q->where('kelas_id', $v))
            ->when($request->search, fn ($q, $v) => $q->where(fn ($w) => $w->where('nama_lengkap', 'like', "%{$v}%")->orWhere('nis', 'like', "%{$v}%")))
            ->when($request->boolean('bermasalah'), fn ($q) => $q->whereHas('pelanggaran'))
            ->orderByDesc('total_poin')->orderBy('nama_lengkap')->orderBy('id')
            ->paginate(15)->withQueryString();

        return view('kedisiplinan.index', ['daftar' => $daftar, 'kelas' => Kelas::orderBy('nama_kelas')->get(), 'ambang' => (int) config('madrasah.ambang_poin')]);
    }

    public function siswa(Siswa $siswa)
    {
        $siswa->load('kelas', 'pelanggaran.jenis', 'pelanggaran.pencatat', 'catatanBk.pencatat');

        return view('kedisiplinan.siswa', [
            'siswa' => $siswa,
            'totalPoin' => (int) $siswa->pelanggaran->sum('poin'),
            'ambang' => (int) config('madrasah.ambang_poin'),
            'jenis' => PelanggaranJenis::orderBy('kategori')->orderBy('nama')->get(),
        ]);
    }

    public function storePelanggaran(Request $request, Siswa $siswa)
    {
        $data = $request->validate([
            'jenis_id' => 'required|integer|exists:pelanggaran_jenis,id',
            'tanggal' => 'required|date|before_or_equal:today',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $siswa->pelanggaran()->create($data + ['poin' => PelanggaranJenis::findOrFail($data['jenis_id'])->poin, 'dicatat_oleh' => $request->user()->id]);

        return redirect()->route('kedisiplinan.siswa', $siswa)->with('success', 'Pelanggaran dicatat.');
    }

    public function destroyPelanggaran(Pelanggaran $pelanggaran)
    {
        $pelanggaran->delete();

        return redirect()->route('kedisiplinan.siswa', $pelanggaran->siswa_id)->with('success', 'Catatan pelanggaran dihapus.');
    }

    public function storeBk(Request $request, Siswa $siswa)
    {
        $data = $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'topik' => 'required|string|max:150',
            'uraian' => 'required|string|max:5000',
            'tindak_lanjut' => 'nullable|string|max:2000',
        ]);

        $siswa->catatanBk()->create($data + ['dicatat_oleh' => $request->user()->id]);

        return redirect()->route('kedisiplinan.siswa', $siswa)->with('success', 'Catatan BK disimpan.');
    }

    public function destroyBk(CatatanBk $catatan)
    {
        $catatan->delete();

        return redirect()->route('kedisiplinan.siswa', $catatan->siswa_id)->with('success', 'Catatan BK dihapus.');
    }

    public function jenis()
    {
        return view('kedisiplinan.jenis', ['jenis' => PelanggaranJenis::withCount('pelanggaran')->orderBy('kategori')->orderBy('nama')->get()]);
    }

    public function storeJenis(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|string|max:100|unique:pelanggaran_jenis,nama',
            'kategori' => 'required|in:'.implode(',', array_keys(PelanggaranJenis::KATEGORI)),
            'poin' => 'required|integer|min:1|max:1000',
        ]);

        PelanggaranJenis::create($data);

        return redirect()->route('kedisiplinan.jenis.index')->with('success', 'Jenis pelanggaran ditambahkan.');
    }

    public function destroyJenis(PelanggaranJenis $jenis)
    {
        if ($jenis->pelanggaran()->exists()) {
            return back()->with('error', 'Jenis ini sudah dipakai pada catatan pelanggaran, jadi tidak dapat dihapus.');
        }

        $jenis->delete();

        return redirect()->route('kedisiplinan.jenis.index')->with('success', 'Jenis pelanggaran dihapus.');
    }
}
