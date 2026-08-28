<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\SuratKeluar;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SuratKeluarController extends Controller
{
    public function index(Request $request): Response
    {
        $sorts = [
            'nomor' => 'nomor_surat',
            'tujuan' => 'tujuan',
            'tanggal' => 'tanggal_kirim',
        ];
        $sort = $sorts[$request->input('sort')] ?? 'tanggal_kirim';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $surat = SuratKeluar::query()
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('tujuan', 'like', "%{$search}%")
                        ->orWhere('perihal', 'like', "%{$search}%");
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (SuratKeluar $surat) => [
                'id' => $surat->id,
                'nomor_surat' => $surat->nomor_surat,
                'tujuan' => $surat->tujuan,
                'perihal' => $surat->perihal,
                'tanggal_kirim' => $surat->tanggal_kirim?->toDateString(),
                'lampiran' => $surat->lampiran,
                'file_draft_url' => $surat->file_draft ? asset('storage/'.$surat->file_draft) : null,
            ]);

        return Inertia::render('SuratKeluar/Index', [
            'surat' => $surat,
            'filters' => [
                'search' => $request->search,
                'sort' => $request->sort,
                'direction' => $request->direction,
            ],
        ]);
    }

    public function create(): Response
    {
        $siswa = Siswa::select('id', 'nama_lengkap', 'nis', 'nisn')->get();

        return Inertia::render('SuratKeluar/Form', [
            'siswa' => $siswa,
        ]);
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
            $file = $request->file('file_draft');
            $filename = time().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('surat-keluar', $filename, 'public');
            $validated['file_draft'] = $path;
        }

        SuratKeluar::create($validated);

        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil disimpan.');
    }

    public function show(SuratKeluar $suratKeluar)
    {
        return view('surat-keluar.show', compact('suratKeluar'));
    }

    public function edit(SuratKeluar $suratKeluar): Response
    {
        $siswa = Siswa::select('id', 'nama_lengkap', 'nis', 'nisn')->get();

        return Inertia::render('SuratKeluar/Form', [
            'suratKeluar' => [
                'id' => $suratKeluar->id,
                'nomor_surat' => $suratKeluar->nomor_surat,
                'tujuan' => $suratKeluar->tujuan,
                'perihal' => $suratKeluar->perihal,
                'tanggal_kirim' => $suratKeluar->tanggal_kirim?->toDateString(),
                'lampiran' => $suratKeluar->lampiran,
                'file_draft_url' => $suratKeluar->file_draft ? asset('storage/'.$suratKeluar->file_draft) : null,
            ],
            'siswa' => $siswa,
        ]);
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
            $file = $request->file('file_draft');
            $filename = time().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('surat-keluar', $filename, 'public');
            $validated['file_draft'] = $path;
        }

        $suratKeluar->update($validated);

        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil diupdate.');
    }

    public function destroy(SuratKeluar $suratKeluar)
    {
        $suratKeluar->delete();

        return redirect()->route('surat-keluar.index')->with('success', 'Surat keluar berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:surat_keluar,id'],
        ]);

        SuratKeluar::whereKey($validated['ids'])->delete();

        return redirect()
            ->route('surat-keluar.index')
            ->with('success', count($validated['ids']).' surat keluar berhasil dihapus.');
    }
}
