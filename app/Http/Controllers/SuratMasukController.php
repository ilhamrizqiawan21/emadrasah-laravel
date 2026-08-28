<?php

namespace App\Http\Controllers;

use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SuratMasukController extends Controller
{
    public function index(Request $request): Response
    {
        $sorts = [
            'agenda' => 'nomor_agenda',
            'asal' => 'asal_surat',
            'tanggal' => 'tanggal_terima',
            'status' => 'status',
        ];
        $sort = $sorts[$request->input('sort')] ?? 'tanggal_terima';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $surat = SuratMasuk::query()
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nomor_agenda', 'like', "%{$search}%")
                        ->orWhere('asal_surat', 'like', "%{$search}%")
                        ->orWhere('nomor_surat', 'like', "%{$search}%")
                        ->orWhere('perihal', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (SuratMasuk $surat) => [
                'id' => $surat->id,
                'nomor_agenda' => $surat->nomor_agenda,
                'asal_surat' => $surat->asal_surat,
                'nomor_surat' => $surat->nomor_surat,
                'perihal' => $surat->perihal,
                'tanggal_terima' => $surat->tanggal_terima?->toDateString(),
                'tanggal_surat' => $surat->tanggal_surat?->toDateString(),
                'status' => $surat->status,
                'file_scan_url' => $surat->file_scan ? asset('storage/'.$surat->file_scan) : null,
            ]);

        return Inertia::render('SuratMasuk/Index', [
            'surat' => $surat,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'sort' => $request->sort,
                'direction' => $request->direction,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('SuratMasuk/Form');
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

        // Handle upload sebelum masuk transaksi
        if ($request->hasFile('file_scan')) {
            $file = $request->file('file_scan');
            $safeName = time().'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                        .'.'.$file->getClientOriginalExtension();
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

            $nomorAgenda = 'SM-'.$tahun.'-'.str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

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

    public function edit(SuratMasuk $suratMasuk): Response
    {
        return Inertia::render('SuratMasuk/Form', [
            'suratMasuk' => [
                'id' => $suratMasuk->id,
                'nomor_agenda' => $suratMasuk->nomor_agenda,
                'asal_surat' => $suratMasuk->asal_surat,
                'nomor_surat' => $suratMasuk->nomor_surat,
                'perihal' => $suratMasuk->perihal,
                'tanggal_terima' => $suratMasuk->tanggal_terima?->toDateString(),
                'tanggal_surat' => $suratMasuk->tanggal_surat?->toDateString(),
                'disposisi' => $suratMasuk->disposisi,
                'status' => $suratMasuk->status,
                'file_scan_url' => $suratMasuk->file_scan ? asset('storage/'.$suratMasuk->file_scan) : null,
            ],
        ]);
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
            $file = $request->file('file_scan');
            $safeName = time().'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                        .'.'.$file->getClientOriginalExtension();
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

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:surat_masuk,id'],
        ]);

        SuratMasuk::whereKey($validated['ids'])->delete();

        return redirect()
            ->route('surat-masuk.index')
            ->with('success', count($validated['ids']).' surat masuk berhasil dihapus.');
    }
}
