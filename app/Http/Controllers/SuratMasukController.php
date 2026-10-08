<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class SuratMasukController extends Controller
{
    public function index()
    {
        $surat = SuratMasuk::orderBy('tanggal_terima', 'desc')->paginate(15);

        return view('surat-masuk.index', compact('surat'));
    }

    public function create()
    {
        return view('surat-masuk.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asal_surat' => 'required',
            'nomor_surat' => 'nullable',
            'perihal' => 'required',
            'tanggal_terima' => 'required|date',
            'tanggal_surat' => 'nullable|date',
            'disposisi' => 'nullable',
            'file_scan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Upload ke disk privat dengan nama acak (ekstensi ditentukan dari isi file,
        // bukan dari nama yang dikirim klien). Disajikan lewat route files.show.
        if ($request->hasFile('file_scan')) {
            $validated['file_scan'] = $request->file('file_scan')->store('surat-masuk', 'local');
        }

        // Nomor agenda diambil dari nomor TERBESAR yang sudah ada (bukan count()),
        // supaya tidak bentrok dengan kolom unik nomor_agenda setelah ada surat yang dihapus.
        // lockForUpdate() menahan request lain sampai transaksi selesai.
        $nomorAgenda = DB::transaction(function () use ($validated, $request) {
            $tahun = date('Y', strtotime($request->tanggal_terima));
            $prefix = 'SM-'.$tahun.'-';

            $lastNumber = SuratMasuk::where('nomor_agenda', 'like', $prefix.'%')
                ->lockForUpdate()
                ->pluck('nomor_agenda')
                ->map(fn ($n) => (int) substr($n, strlen($prefix)))
                ->max() ?? 0;

            $nomorAgenda = $prefix.str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            SuratMasuk::create(array_merge($validated, [
                'nomor_agenda' => $nomorAgenda,
                'status' => 'diterima',
            ]));

            return $nomorAgenda;
        });

        return redirect()
            ->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil disimpan. Nomor agenda: '.$nomorAgenda);
    }

    public function show(SuratMasuk $suratMasuk)
    {
        return view('surat-masuk.show', compact('suratMasuk'));
    }

    public function edit(SuratMasuk $suratMasuk)
    {
        return view('surat-masuk.edit', compact('suratMasuk'));
    }

    public function update(Request $request, SuratMasuk $suratMasuk)
    {
        $validated = $request->validate([
            'asal_surat' => 'required',
            'nomor_surat' => 'nullable',
            'perihal' => 'required',
            'tanggal_terima' => 'required|date',
            'tanggal_surat' => 'nullable|date',
            'disposisi' => 'nullable',
            'status' => 'required|in:diterima,diproses,selesai',
            'file_scan' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('file_scan')) {
            $validated['file_scan'] = $request->file('file_scan')->store('surat-masuk', 'local');
            if ($suratMasuk->file_scan) {
                Storage::disk('local')->delete($suratMasuk->file_scan);
            }
        }

        $suratMasuk->update($validated);

        return redirect()
            ->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil diupdate.');
    }

    public function destroy(SuratMasuk $suratMasuk)
    {
        if ($suratMasuk->file_scan) {
            Storage::disk('local')->delete($suratMasuk->file_scan);
        }
        $suratMasuk->delete();

        return redirect()
            ->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil dihapus.');
    }
}
