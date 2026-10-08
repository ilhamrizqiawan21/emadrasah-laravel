<?php

namespace App\Http\Controllers;

use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SuratKeluarController extends Controller
{
    public function index()
    {
        $surat = SuratKeluar::orderBy('tanggal_kirim', 'desc')->orderByDesc('id')->paginate(15);

        return view('surat-keluar.index', compact('surat'));
    }

    public function create()
    {
        return view('surat-keluar.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_surat' => 'required|unique:surat_keluar',
            'tujuan' => 'required',
            'perihal' => 'required',
            'tanggal_kirim' => 'required|date',
            'lampiran' => 'nullable',
            'file_draft' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($request->hasFile('file_draft')) {
            $validated['file_draft'] = $request->file('file_draft')->store('surat-keluar', 'local');

        }

        SuratKeluar::create($validated);

        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil disimpan.');
    }

    public function show(SuratKeluar $suratKeluar)
    {
        return view('surat-keluar.show', compact('suratKeluar'));
    }

    public function edit(SuratKeluar $suratKeluar)
    {
        return view('surat-keluar.edit', compact('suratKeluar'));
    }

    public function update(Request $request, SuratKeluar $suratKeluar)
    {
        $validated = $request->validate([
            'nomor_surat' => 'required|unique:surat_keluar,nomor_surat,'.$suratKeluar->id,
            'tujuan' => 'required',
            'perihal' => 'required',
            'tanggal_kirim' => 'required|date',
            'lampiran' => 'nullable',
            'file_draft' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        if ($request->hasFile('file_draft')) {
            $validated['file_draft'] = $request->file('file_draft')->store('surat-keluar', 'local');
            if ($suratKeluar->file_draft) {
                Storage::disk('local')->delete($suratKeluar->file_draft);
            }
        }

        $suratKeluar->update($validated);

        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil diupdate.');
    }

    public function destroy(SuratKeluar $suratKeluar)
    {
        if ($suratKeluar->file_draft) {
            Storage::disk('local')->delete($suratKeluar->file_draft);
        }
        $suratKeluar->delete();

        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil dihapus.');
    }
}
