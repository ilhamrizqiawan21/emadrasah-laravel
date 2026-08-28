<?php

namespace App\Http\Controllers;

use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TahunPelajaranController extends Controller
{
    public function index(Request $request): Response
    {
        $tahunPelajaran = TahunPelajaran::query()
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('kode', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($request->input('sort'), ['kode', 'nama', 'is_aktif'], true),
                fn ($query) => $query->orderBy($request->input('sort'), $request->input('direction') === 'desc' ? 'desc' : 'asc'),
                fn ($query) => $query->orderByDesc('is_aktif')->orderBy('kode')
            )
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('TahunPelajaran/Index', [
            'tahunPelajaran' => $tahunPelajaran,
            'filters' => [
                'search' => $request->search,
                'sort' => $request->sort,
                'direction' => $request->direction,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('TahunPelajaran/Form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', 'unique:tahun_pelajaran,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'is_aktif' => ['sometimes', 'boolean'],
        ]);

        TahunPelajaran::create([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'is_aktif' => $request->boolean('is_aktif'),
        ]);

        return redirect()->route('tahun-pelajaran.index')->with('success', 'Tahun pelajaran berhasil ditambahkan.');
    }

    public function edit(TahunPelajaran $tahunPelajaran): Response
    {
        return Inertia::render('TahunPelajaran/Form', [
            'tahunPelajaran' => $tahunPelajaran->only(['id', 'kode', 'nama', 'is_aktif']),
        ]);
    }

    public function update(Request $request, TahunPelajaran $tahunPelajaran)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', Rule::unique('tahun_pelajaran', 'kode')->ignore($tahunPelajaran->id)],
            'nama' => ['required', 'string', 'max:255'],
            'is_aktif' => ['sometimes', 'boolean'],
        ]);

        $tahunPelajaran->update([
            'kode' => $validated['kode'],
            'nama' => $validated['nama'],
            'is_aktif' => $request->boolean('is_aktif'),
        ]);

        return redirect()->route('tahun-pelajaran.index')->with('success', 'Tahun pelajaran berhasil diupdate.');
    }

    public function destroy(TahunPelajaran $tahunPelajaran)
    {
        $tahunPelajaran->delete();

        return redirect()->route('tahun-pelajaran.index')->with('success', 'Tahun pelajaran berhasil dihapus.');
    }
}
