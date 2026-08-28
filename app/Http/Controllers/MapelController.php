<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MapelController extends Controller
{
    public function index(Request $request): Response
    {
        $mapels = Mapel::with('parent')
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nama_mapel', 'like', "%{$search}%")
                        ->orWhereHas('parent', fn ($query) => $query->where('nama_mapel', 'like', "%{$search}%"));
                });
            })
            ->when(
                in_array($request->input('sort'), ['nama_mapel', 'jp_per_sesi', 'urut'], true),
                fn ($query) => $query->orderBy($request->input('sort'), $request->input('direction') === 'desc' ? 'desc' : 'asc'),
                fn ($query) => $query->orderByRaw('parent_id is not null')->orderBy('urut')->orderBy('nama_mapel')
            )
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Mapel $mapel) => [
                'id' => $mapel->id,
                'nama_mapel' => $mapel->nama_mapel,
                'jp_per_sesi' => $mapel->jp_per_sesi,
                'parent_id' => $mapel->parent_id,
                'parent' => $mapel->parent ? [
                    'id' => $mapel->parent->id,
                    'nama_mapel' => $mapel->parent->nama_mapel,
                ] : null,
                'urut' => $mapel->urut,
            ]);

        return Inertia::render('Mapel/Index', [
            'mapels' => $mapels,
            'filters' => [
                'search' => $request->search,
                'sort' => $request->sort,
                'direction' => $request->direction,
            ],
        ]);
    }

    public function create(): Response
    {
        $parents = Mapel::whereNull('parent_id')->orderBy('nama_mapel')->get(['id', 'nama_mapel']);

        return Inertia::render('Mapel/Form', [
            'parents' => $parents,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_mapel' => ['required', 'string', 'max:255', 'unique:mapels,nama_mapel'],
            'jp_per_sesi' => ['nullable', 'integer', 'min:0'],
            'parent_id' => ['nullable', 'exists:mapels,id'],
            'urut' => ['nullable', 'integer', 'min:1'],
        ]);
        Mapel::create($validated);

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Mapel $mapel): Response
    {
        $parents = Mapel::whereNull('parent_id')->where('id', '!=', $mapel->id)->orderBy('nama_mapel')->get(['id', 'nama_mapel']);

        return Inertia::render('Mapel/Form', [
            'mapel' => $mapel->only(['id', 'nama_mapel', 'jp_per_sesi', 'parent_id', 'urut']),
            'parents' => $parents,
        ]);
    }

    public function update(Request $request, Mapel $mapel)
    {
        $validated = $request->validate([
            'nama_mapel' => ['required', 'string', 'max:255', Rule::unique('mapels', 'nama_mapel')->ignore($mapel->id)],
            'jp_per_sesi' => ['nullable', 'integer', 'min:0'],
            'parent_id' => ['nullable', 'exists:mapels,id'],
            'urut' => ['nullable', 'integer', 'min:1'],
        ]);
        $mapel->update($validated);

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil diupdate.');
    }

    public function destroy(Mapel $mapel)
    {
        $mapel->delete();

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
