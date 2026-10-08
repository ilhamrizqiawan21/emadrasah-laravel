<?php

namespace App\Http\Controllers;

use App\Models\TemplateSurat;
use Illuminate\Http\Request;

class TemplateSuratController extends Controller
{
    public function index()
    {
        $templates = TemplateSurat::orderBy('nama_template')->paginate(10);
        return view('template-surat.index', compact('templates'));
    }

    public function create()
    {
        return view('template-surat.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_template' => 'required|unique:template_surat',
            'konten' => 'required',
        ]);
        TemplateSurat::create($request->only('nama_template', 'konten'));
        return redirect()->route('template-surat.index')->with('success', 'Template surat berhasil ditambahkan.');
    }

    public function edit(TemplateSurat $templateSurat)
    {
        return view('template-surat.edit', compact('templateSurat'));
    }

    public function update(Request $request, TemplateSurat $templateSurat)
    {
        $request->validate([
            'nama_template' => 'required|unique:template_surat,nama_template,' . $templateSurat->id,
            'konten' => 'required',
        ]);
        $templateSurat->update($request->only('nama_template', 'konten'));
        return redirect()->route('template-surat.index')->with('success', 'Template surat berhasil diupdate.');
    }

    public function destroy(TemplateSurat $templateSurat)
    {
        $templateSurat->delete();
        return redirect()->route('template-surat.index')->with('success', 'Template surat berhasil dihapus.');
    }
}