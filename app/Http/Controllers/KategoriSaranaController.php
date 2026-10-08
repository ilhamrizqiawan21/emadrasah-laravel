<?php

namespace App\Http\Controllers;

use App\Models\KategoriSarana;
use Illuminate\Http\Request;

class KategoriSaranaController extends Controller
{
    public function index()
    {
        $kategori = KategoriSarana::orderBy('nama_kategori')->orderBy('id')->paginate(10);

        return view('kategori-sarana.index', compact('kategori'));
    }

    public function create()
    {
        return view('kategori-sarana.create');
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
        return view('kategori-sarana.edit', compact('kategori_sarana'));
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
