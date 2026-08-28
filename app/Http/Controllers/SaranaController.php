<?php

namespace App\Http\Controllers;

use App\Models\KategoriSarana;
use App\Models\PemeliharaanSarana;
use App\Models\PeminjamanSarana;
use App\Models\SaranaPrasarana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaranaController extends Controller
{
    public function index(Request $request)
    {
        $sorts = [
            'kode' => 'kode_sarana',
            'nama' => 'nama_sarana',
            'kategori' => 'kategori_id',
            'jumlah' => 'jumlah',
            'stok' => 'stok_tersedia',
            'kondisi' => 'kondisi',
            'lokasi' => 'lokasi_ruang',
        ];
        $sort = $sorts[$request->input('sort')] ?? 'nama_sarana';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $sarana = SaranaPrasarana::with('kategori')
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('kode_sarana', 'like', "%{$search}%")
                        ->orWhere('nama_sarana', 'like', "%{$search}%")
                        ->orWhere('lokasi_ruang', 'like', "%{$search}%")
                        ->orWhereHas('kategori', fn ($query) => $query->where('nama_kategori', 'like', "%{$search}%"));
                });
            })
            ->when($request->kategori_id, fn ($query, string $kategoriId) => $query->where('kategori_id', $kategoriId))
            ->when($request->kondisi, fn ($query, string $kondisi) => $query->where('kondisi', $kondisi))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();
        $kategori = KategoriSarana::orderBy('nama_kategori')->get();

        return view('sarana.index', compact('sarana', 'kategori'));
    }

    public function create()
    {
        $kategori = KategoriSarana::all();

        return view('sarana.create', compact('kategori'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_sarana' => 'required|unique:sarana_prasarana',
            'nama_sarana' => 'required',
            'kategori_id' => 'required|exists:kategori_sarana,id',
            'spesifikasi' => 'nullable',
            'jumlah' => 'required|integer|min:1',
            'stok_tersedia' => 'nullable|integer|min:0',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
            'lokasi_ruang' => 'nullable',
            'tahun_pengadaan' => 'nullable|digits:4',
            'foto' => 'nullable|image|max:2048',
        ]);

        $validated['stok_tersedia'] = $validated['stok_tersedia'] ?? $validated['jumlah'];

        if ($validated['stok_tersedia'] > $validated['jumlah']) {
            throw ValidationException::withMessages([
                'stok_tersedia' => 'Stok tersedia tidak boleh melebihi jumlah sarana.',
            ]);
        }

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('sarana', $filename, 'public');
            $validated['foto'] = $path;
        }

        SaranaPrasarana::create($validated);

        return redirect()->route('sarana.index')->with('success', 'Sarana berhasil ditambahkan.');
    }

    public function edit(SaranaPrasarana $sarana)
    {
        $kategori = KategoriSarana::all();

        return view('sarana.edit', compact('sarana', 'kategori'));
    }

    public function update(Request $request, SaranaPrasarana $sarana)
    {
        $validated = $request->validate([
            'kode_sarana' => 'required|unique:sarana_prasarana,kode_sarana,'.$sarana->id,
            'nama_sarana' => 'required',
            'kategori_id' => 'required|exists:kategori_sarana,id',
            'spesifikasi' => 'nullable',
            'jumlah' => 'required|integer|min:1',
            'stok_tersedia' => 'nullable|integer|min:0',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
            'lokasi_ruang' => 'nullable',
            'tahun_pengadaan' => 'nullable|digits:4',
            'foto' => 'nullable|image|max:2048',
        ]);

        $validated['stok_tersedia'] = $validated['stok_tersedia'] ?? $sarana->stok_tersedia;

        if ($validated['stok_tersedia'] > $validated['jumlah']) {
            throw ValidationException::withMessages([
                'stok_tersedia' => 'Stok tersedia tidak boleh melebihi jumlah sarana.',
            ]);
        }

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('sarana', $filename, 'public');
            $validated['foto'] = $path;
        }

        $sarana->update($validated);

        return redirect()->route('sarana.index')->with('success', 'Sarana berhasil diupdate.');
    }

    public function destroy(SaranaPrasarana $sarana)
    {
        $sarana->delete();

        return redirect()->route('sarana.index')->with('success', 'Sarana berhasil dihapus.');
    }

    public function peminjaman(SaranaPrasarana $sarana)
    {
        $peminjaman = PeminjamanSarana::where('sarana_id', $sarana->id)->orderBy('tanggal_pinjam', 'desc')->paginate(10);

        return view('sarana.peminjaman', compact('sarana', 'peminjaman'));
    }

    public function storePeminjaman(Request $request, SaranaPrasarana $sarana)
    {
        $validated = $request->validate([
            'peminjam' => 'required',
            'tipe_peminjam' => 'required|in:guru,siswa',
            'tanggal_pinjam' => 'required|date',
        ]);

        DB::transaction(function () use ($validated, $sarana) {
            $lockedSarana = SaranaPrasarana::whereKey($sarana->id)->lockForUpdate()->firstOrFail();

            if ($lockedSarana->stok_tersedia < 1) {
                throw ValidationException::withMessages([
                    'sarana_id' => 'Stok sarana tidak tersedia untuk dipinjam.',
                ]);
            }

            PeminjamanSarana::create($validated + [
                'sarana_id' => $lockedSarana->id,
                'status' => 'dipinjam',
            ]);

            $lockedSarana->decrement('stok_tersedia');
        });

        return redirect()->route('sarana.peminjaman', $sarana)->with('success', 'Peminjaman dicatat.');
    }

    public function kembalikan(PeminjamanSarana $peminjaman)
    {
        DB::transaction(function () use ($peminjaman) {
            $lockedPeminjaman = PeminjamanSarana::whereKey($peminjaman->id)->lockForUpdate()->firstOrFail();

            if ($lockedPeminjaman->status === 'dikembalikan') {
                throw ValidationException::withMessages([
                    'peminjaman' => 'Sarana ini sudah dikembalikan.',
                ]);
            }

            $lockedSarana = SaranaPrasarana::whereKey($lockedPeminjaman->sarana_id)->lockForUpdate()->firstOrFail();

            $lockedPeminjaman->update([
                'tanggal_kembali' => today(),
                'status' => 'dikembalikan',
            ]);

            if ($lockedSarana->stok_tersedia < $lockedSarana->jumlah) {
                $lockedSarana->increment('stok_tersedia');
            }
        });

        return back()->with('success', 'Sarana dikembalikan.');
    }

    public function pemeliharaan(SaranaPrasarana $sarana)
    {
        $pemeliharaan = PemeliharaanSarana::where('sarana_id', $sarana->id)->orderBy('tanggal_pemeliharaan', 'desc')->paginate(10);

        return view('sarana.pemeliharaan', compact('sarana', 'pemeliharaan'));
    }

    public function storePemeliharaan(Request $request, SaranaPrasarana $sarana)
    {
        $validated = $request->validate([
            'tanggal_pemeliharaan' => 'required|date',
            'biaya' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable',
            'status' => 'nullable|in:proses,selesai',
            'tanggal_selesai' => 'nullable|date',
            'teknisi' => 'nullable',
        ]);
        $validated['sarana_id'] = $sarana->id;
        PemeliharaanSarana::create($validated);

        return redirect()->route('sarana.pemeliharaan', $sarana)->with('success', 'Pemeliharaan dicatat.');
    }
}
