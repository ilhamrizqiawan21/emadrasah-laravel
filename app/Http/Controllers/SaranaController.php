<?php

namespace App\Http\Controllers;

use App\Models\SaranaPrasarana;
use App\Models\KategoriSarana;
use App\Models\PeminjamanSarana;
use App\Models\PemeliharaanSarana;
use Illuminate\Http\Request;

class SaranaController extends Controller
{
    public function index()
    {
        $sarana = SaranaPrasarana::with('kategori')->orderBy('nama_sarana')->paginate(15);
        return view('sarana.index', compact('sarana'));
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

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
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
            'kode_sarana' => 'required|unique:sarana_prasarana,kode_sarana,' . $sarana->id,
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

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $filename = time() . '_' . $file->getClientOriginalName();
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
        $validated['sarana_id'] = $sarana->id;
        $validated['status'] = 'dipinjam';
        PeminjamanSarana::create($validated);
        return redirect()->route('sarana.peminjaman', $sarana)->with('success', 'Peminjaman dicatat.');
    }

    public function kembalikan(PeminjamanSarana $peminjaman)
    {
        $peminjaman->update([
            'tanggal_kembali' => today(),
            'status' => 'dikembalikan',
        ]);
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
