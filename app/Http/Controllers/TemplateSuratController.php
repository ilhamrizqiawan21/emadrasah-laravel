<?php

namespace App\Http\Controllers;

use App\Models\TemplateSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TemplateSuratController extends Controller
{
    public function index(): Response
    {
        $templates = TemplateSurat::orderBy('nama_template')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (TemplateSurat $template) => [
                'id' => $template->id,
                'nama_template' => $template->nama_template,
                'konten' => $template->konten,
                'preview' => Str::limit(strip_tags($template->konten), 100),
            ]);

        return Inertia::render('TemplateSurat/Index', [
            'templates' => $templates,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('TemplateSurat/Form');
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

    public function edit(TemplateSurat $templateSurat): Response
    {
        return Inertia::render('TemplateSurat/Form', [
            'templateSurat' => [
                'id' => $templateSurat->id,
                'nama_template' => $templateSurat->nama_template,
                'konten' => $templateSurat->konten,
            ],
        ]);
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
