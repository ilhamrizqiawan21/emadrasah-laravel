<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Pembayaran;
use App\Models\Siswa;
use App\Models\Tagihan;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Tagihan (SPP dan sejenisnya) per siswa dan pencatatan pembayarannya oleh admin/operator. */
class KeuanganController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:belum,sebagian,lunas,terlambat',
            'kelas_id' => 'nullable|integer|exists:kelas,id',
            'jenis' => 'nullable|in:'.implode(',', Tagihan::JENIS),
            'periode' => ['nullable', 'date_format:Y-m'],
            'search' => 'nullable|string|max:100',
        ]);

        $saring = Tagihan::query()
            ->when($request->status, fn ($q, $v) => $q->status($v))
            ->when($request->jenis, fn ($q, $v) => $q->where('jenis', $v))
            ->when($request->periode, fn ($q, $v) => $q->where('periode', $v))
            ->when($request->kelas_id, fn ($q, $v) => $q->whereHas('siswa', fn ($s) => $s->where('kelas_id', $v)))
            ->when($request->search, fn ($q, $v) => $q->whereHas('siswa', fn ($s) => $s->where(fn ($w) => $w
                ->where('nama_lengkap', 'like', "%{$v}%")->orWhere('nis', 'like', "%{$v}%"))));

        $tagihan = (clone $saring)->sum('jumlah');
        $terbayar = (int) Pembayaran::whereIn('tagihan_id', (clone $saring)->select('tagihan.id'))->sum('jumlah');

        return view('keuangan.index', [
            'daftar' => $saring->with('siswa.kelas')->denganTerbayar()->orderBy('jatuh_tempo')->orderBy('id')->paginate(15)->withQueryString(),
            'ringkasan' => ['tagihan' => (int) $tagihan, 'terbayar' => $terbayar, 'tunggakan' => max(0, (int) $tagihan - $terbayar)],
            'kelas' => Kelas::orderBy('nama_kelas')->get(),
        ]);
    }

    /** Buat tagihan massal untuk siswa aktif (satu kelas atau semua); siswa yang sudah punya tagihan sama dilewati. */
    public function generate(Request $request)
    {
        $data = $request->validate([
            'kelas_id' => 'nullable|integer|exists:kelas,id',
            'jenis' => 'required|in:'.implode(',', Tagihan::JENIS),
            'periode' => ['required_if:jenis,SPP', 'nullable', 'date_format:Y-m'],
            'jumlah' => 'required|integer|min:1|max:100000000',
            'jatuh_tempo' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $siswaIds = Siswa::where('status', 'Aktif')
            ->when($data['kelas_id'] ?? null, fn ($q, $v) => $q->where('kelas_id', $v))
            ->pluck('id');

        $sudah = Tagihan::where('jenis', $data['jenis'])
            ->when($data['periode'] ?? null, fn ($q, $v) => $q->where('periode', $v), fn ($q) => $q->whereNull('periode'))
            ->whereIn('siswa_id', $siswaIds)->pluck('siswa_id');
        $baru = $siswaIds->diff($sudah);

        $now = now();
        foreach ($baru->chunk(500) as $potong) {
            Tagihan::insert($potong->map(fn ($id) => [
                'siswa_id' => $id, 'jenis' => $data['jenis'], 'periode' => $data['periode'] ?? null, 'jumlah' => $data['jumlah'],
                'jatuh_tempo' => $data['jatuh_tempo'], 'keterangan' => $data['keterangan'] ?? null, 'created_at' => $now, 'updated_at' => $now,
            ])->all());
        }

        return redirect()->route('keuangan.index')
            ->with('success', "{$baru->count()} tagihan dibuat".($sudah->isNotEmpty() ? ", {$sudah->count()} dilewati karena sudah ada." : '.'));
    }

    public function show(Tagihan $tagihan)
    {
        $tagihan->load('siswa.kelas', 'pembayaran.pencatat');

        return view('keuangan.show', compact('tagihan'));
    }

    public function destroy(Tagihan $tagihan)
    {
        if ($tagihan->pembayaran()->exists()) {
            return back()->with('error', 'Tagihan yang sudah ada pembayarannya tidak dapat dihapus. Batalkan pembayarannya dulu.');
        }

        $tagihan->delete();

        return redirect()->route('keuangan.index')->with('success', 'Tagihan dihapus.');
    }

    public function bayar(Request $request, Tagihan $tagihan)
    {
        $data = $request->validate([
            'jumlah' => 'required|integer|min:1|max:100000000',
            'tanggal' => 'required|date|before_or_equal:today',
            'metode' => 'required|in:'.implode(',', array_keys(Pembayaran::METODE)),
            'catatan' => 'nullable|string|max:255',
        ]);

        // Kunci baris tagihan agar dua kasir yang membayar bersamaan tidak melampaui sisa.
        $ok = DB::transaction(function () use ($tagihan, $data, $request) {
            $kunci = Tagihan::whereKey($tagihan->id)->lockForUpdate()->firstOrFail();
            if ($data['jumlah'] > $kunci->sisa()) {
                return false;
            }
            $kunci->pembayaran()->create($data + ['dicatat_oleh' => $request->user()->id]);

            return true;
        });

        if (! $ok) {
            return back()->withInput()->withErrors(['jumlah' => 'Jumlah melebihi sisa tagihan (Rp '.number_format($tagihan->sisa(), 0, ',', '.').').']);
        }

        return redirect()->route('keuangan.tagihan.show', $tagihan)->with('success', 'Pembayaran dicatat.');
    }

    public function kuitansi(Pembayaran $pembayaran)
    {
        return $this->unduhKuitansi($pembayaran);
    }

    /** Dipakai juga portal wali; pemanggil wajib memastikan hak aksesnya lebih dulu. */
    public static function unduhKuitansi(Pembayaran $pembayaran)
    {
        $pembayaran->load('tagihan.siswa.kelas', 'pencatat');

        return Pdf::loadView('keuangan.kuitansi', compact('pembayaran'))->setPaper('a5', 'landscape')->stream('Kuitansi_'.$pembayaran->nomorKuitansi().'.pdf');
    }

    public function batalkanBayar(Pembayaran $pembayaran)
    {
        $tagihanId = $pembayaran->tagihan_id;
        $pembayaran->delete();

        return redirect()->route('keuangan.tagihan.show', $tagihanId)->with('success', 'Pembayaran dibatalkan.');
    }
}
