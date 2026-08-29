<?php

namespace App\Http\Controllers;

use App\Models\KategoriSarana;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KategoriSaranaController extends Controller
{
    public function index(Request $request)
    {
        $kategori = KategoriSarana::query()
            ->when($request->search, fn ($query, string $search) => $query->where('nama_kategori', 'like', "%{$search}%"))
            ->orderBy('nama_kategori')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('KategoriSarana/Index', [
            'kategori' => $kategori,
            'filters' => $request->only(['search']),
        ]);
    }

    public function create()
    {
        return Inertia::render('KategoriSarana/Form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|unique:kategori_sarana',
        ]);
        KategoriSarana::create($request->only('nama_kategori'));

        return redirect()->route('kategori-sarana.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(KategoriSarana $kategori_sarana)
    {
        return Inertia::render('KategoriSarana/Form', [
            'kategoriSarana' => $kategori_sarana,
        ]);
    }

    public function update(Request $request, KategoriSarana $kategori_sarana)
    {
        $request->validate([
            'nama_kategori' => 'required|unique:kategori_sarana,nama_kategori,'.$kategori_sarana->id,
        ]);
        $kategori_sarana->update($request->only('nama_kategori'));

        return redirect()->route('kategori-sarana.index')->with('success', 'Kategori berhasil diupdate.');
    }

    public function destroy(KategoriSarana $kategori_sarana)
    {
        if ($kategori_sarana->sarana()->count() > 0) {
            return redirect()->route('kategori-sarana.index')->with('error', 'Kategori tidak dapat dihapus karena masih digunakan.');
        }
        $kategori_sarana->delete();

        return redirect()->route('kategori-sarana.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
