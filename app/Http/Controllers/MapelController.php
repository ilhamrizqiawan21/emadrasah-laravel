<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use Illuminate\Http\Request;

class MapelController extends Controller
{
    public function index()
    {
        $mapels = Mapel::with('parent')->orderBy('nama_mapel')->paginate(10);

        return view('mapel.index', compact('mapels'));
    }

    public function create()
    {
        $parents = Mapel::whereNull('parent_id')->orderBy('nama_mapel')->get();

        return view('mapel.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_mapel' => 'required|unique:mapels',
            'jp_per_sesi' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:mapels,id',
            'urut' => 'nullable|integer|min:1',
        ]);
        Mapel::create($request->only(['nama_mapel', 'jp_per_sesi', 'parent_id', 'urut']));

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function edit(Mapel $mapel)
    {
        $parents = Mapel::whereNull('parent_id')->where('id', '!=', $mapel->id)->orderBy('nama_mapel')->get();

        return view('mapel.edit', compact('mapel', 'parents'));
    }

    public function update(Request $request, Mapel $mapel)
    {
        $request->validate([
            'nama_mapel' => 'required|unique:mapels,nama_mapel,'.$mapel->id,
            'jp_per_sesi' => 'nullable|integer|min:0',
            'parent_id' => 'nullable|exists:mapels,id',
            'urut' => 'nullable|integer|min:1',
        ]);
        $mapel->update($request->only(['nama_mapel', 'jp_per_sesi', 'parent_id', 'urut']));

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil diupdate.');
    }

    public function destroy(Mapel $mapel)
    {
        $mapel->delete();

        return redirect()->route('mapel.index')->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
