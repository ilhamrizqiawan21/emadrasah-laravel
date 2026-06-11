<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            'asal_surat'     => 'required',
            'nomor_surat'    => 'nullable',
            'perihal'        => 'required',
            'tanggal_terima' => 'required|date',
            'tanggal_surat'  => 'nullable|date',
            'disposisi'      => 'nullable',
            'file_scan'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        // Handle upload sebelum masuk transaksi
        if ($request->hasFile('file_scan')) {
            $file     = $request->file('file_scan');
            $safeName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $file->getClientOriginalExtension();
            $validated['file_scan'] = $file->storeAs('surat-masuk', $safeName, 'public');
        }

        // FIX POIN 2 — bungkus di dalam DB::transaction + lockForUpdate()
        // agar tidak ada dua request yang mengambil count() yang sama secara
        // bersamaan (race condition) dan menghasilkan nomor agenda duplikat.
        $nomorAgenda = DB::transaction(function () use ($validated, $request) {
            $tahun = date('Y', strtotime($request->tanggal_terima));

            // lockForUpdate() menahan row lain sampai transaksi selesai
            $lastNumber = SuratMasuk::whereYear('tanggal_terima', $tahun)
                ->lockForUpdate()
                ->count();

            $nomorAgenda = 'SM-' . $tahun . '-' . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

            SuratMasuk::create(array_merge($validated, [
                'nomor_agenda' => $nomorAgenda,
                'status'       => 'diterima',
            ]));

            return $nomorAgenda;
        });

        return redirect()
            ->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil disimpan. Nomor agenda: ' . $nomorAgenda);
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
            'asal_surat'     => 'required',
            'nomor_surat'    => 'nullable',
            'perihal'        => 'required',
            'tanggal_terima' => 'required|date',
            'tanggal_surat'  => 'nullable|date',
            'disposisi'      => 'nullable',
            'status'         => 'required|in:diterima,diproses,selesai',
            'file_scan'      => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('file_scan')) {
            $file     = $request->file('file_scan');
            $safeName = time() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                        . '.' . $file->getClientOriginalExtension();
            $validated['file_scan'] = $file->storeAs('surat-masuk', $safeName, 'public');
        }

        $suratMasuk->update($validated);

        return redirect()
            ->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil diupdate.');
    }

    public function destroy(SuratMasuk $suratMasuk)
    {
        $suratMasuk->delete();

        return redirect()
            ->route('surat-masuk.index')
            ->with('success', 'Surat masuk berhasil dihapus.');
    }
}