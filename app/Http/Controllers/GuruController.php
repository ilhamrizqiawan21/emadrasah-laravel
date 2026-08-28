<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class GuruController extends Controller
{
    public function index(Request $request): Response
    {
        $gurus = Guru::query()
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('kode', 'like', "%{$search}%")
                        ->orWhere('nama', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%")
                        ->orWhere('bidang_studi', 'like', "%{$search}%");
                });
            })
            ->when(
                in_array($request->input('sort'), ['kode', 'nip', 'nama', 'bidang_studi', 'beban_jp'], true),
                fn ($query) => $query->orderBy($request->input('sort'), $request->input('direction') === 'desc' ? 'desc' : 'asc'),
                fn ($query) => $query->orderByRaw('LENGTH(kode), kode')
            )
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Guru/Index', [
            'gurus' => $gurus,
            'filters' => [
                'search' => $request->search,
                'sort' => $request->sort,
                'direction' => $request->direction,
            ],
        ]);
    }

    public function create(): Response
    {
        $mapels = Mapel::orderBy('nama_mapel')->pluck('nama_mapel')->values();

        return Inertia::render('Guru/Form', [
            'mapels' => $mapels,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', 'unique:gurus,kode'],
            'nama' => ['required', 'string', 'max:255'],
            'bidang_studi' => ['nullable', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50', 'unique:gurus,nip'],
            'email' => ['nullable', 'email', 'max:255', 'unique:gurus,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'beban_jp' => ['nullable', 'integer', 'min:0'],
        ]);

        Guru::create($validated);

        return redirect()->route('guru.index')->with('success', 'Guru berhasil ditambahkan.');
    }

    public function edit(Guru $guru): Response
    {
        $mapels = Mapel::orderBy('nama_mapel')->pluck('nama_mapel')->values();

        return Inertia::render('Guru/Form', [
            'guru' => $guru->only(['id', 'kode', 'nama', 'bidang_studi', 'nip', 'email', 'phone', 'beban_jp']),
            'mapels' => $mapels,
        ]);
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', Rule::unique('gurus', 'kode')->ignore($guru->id)],
            'nama' => ['required', 'string', 'max:255'],
            'bidang_studi' => ['nullable', 'string', 'max:255'],
            'nip' => ['nullable', 'string', 'max:50', Rule::unique('gurus', 'nip')->ignore($guru->id)],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('gurus', 'email')->ignore($guru->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'beban_jp' => ['nullable', 'integer', 'min:0'],
        ]);

        $guru->update($validated);

        return redirect()->route('guru.index')->with('success', 'Guru berhasil diupdate.');
    }

    public function destroy(Guru $guru)
    {
        // Cek apakah guru masih memiliki jadwal atau relasi lain
        if ($guru->jadwals()->count() > 0) {
            return redirect()->route('guru.index')->with('error', 'Guru masih memiliki jadwal mengajar. Hapus jadwal terlebih dahulu.');
        }
        if ($guru->agendas()->count() > 0) {
            return redirect()->route('guru.index')->with('error', 'Guru masih memiliki riwayat absensi. Hapus terlebih dahulu.');
        }
        $guru->delete();

        return redirect()->route('guru.index')->with('success', 'Guru berhasil dihapus.');
    }
}
