<?php

namespace App\Http\Controllers;

use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class KelasController extends Controller
{
    public function index(Request $request): Response
    {
        $kelas = Kelas::with('guruPembimbing')
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nama_kelas', 'like', "%{$search}%")
                        ->orWhere('tingkat', 'like', "%{$search}%")
                        ->orWhere('ruangan', 'like', "%{$search}%")
                        ->orWhereHas('guruPembimbing', fn ($query) => $query->where('nama', 'like', "%{$search}%"));
                });
            })
            ->when(
                in_array($request->input('sort'), ['nama_kelas', 'tingkat', 'ruangan', 'kapasitas', 'fase'], true),
                fn ($query) => $query->orderBy($request->input('sort'), $request->input('direction') === 'desc' ? 'desc' : 'asc'),
                fn ($query) => $query->orderBy('tingkat')->orderBy('nama_kelas')
            )
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Kelas $kelas) => [
                'id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
                'tingkat' => $kelas->tingkat,
                'guru_pembimbing_id' => $kelas->guru_pembimbing_id,
                'guru_pembimbing' => $kelas->guruPembimbing ? [
                    'id' => $kelas->guruPembimbing->id,
                    'nama' => $kelas->guruPembimbing->nama,
                ] : null,
                'kapasitas' => $kelas->kapasitas,
                'ruangan' => $kelas->ruangan,
                'fase' => $kelas->fase,
            ]);

        return Inertia::render('Kelas/Index', [
            'kelas' => $kelas,
            'filters' => [
                'search' => $request->search,
                'sort' => $request->sort,
                'direction' => $request->direction,
            ],
        ]);
    }

    public function create(): Response
    {
        $gurus = Guru::orderBy('nama')->get(['id', 'nama', 'kode']);

        return Inertia::render('Kelas/Form', [
            'gurus' => $gurus,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:50', 'unique:kelas,nama_kelas'],
            'tingkat' => ['required', 'string', 'max:10'],
            'guru_pembimbing_id' => ['nullable', 'exists:gurus,id'],
            'kapasitas' => ['nullable', 'integer', 'min:1'],
            'ruangan' => ['nullable', 'string', 'max:100'],
            'fase' => ['nullable', 'string', 'max:5'],
        ]);
        Kelas::create($validated);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(Kelas $kelas): Response
    {
        $gurus = Guru::orderBy('nama')->get(['id', 'nama', 'kode']);

        return Inertia::render('Kelas/Form', [
            'kelas' => $kelas->only(['id', 'nama_kelas', 'tingkat', 'guru_pembimbing_id', 'kapasitas', 'ruangan', 'fase']),
            'gurus' => $gurus,
        ]);
    }

    public function update(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'nama_kelas' => ['required', 'string', 'max:50', Rule::unique('kelas', 'nama_kelas')->ignore($kelas->id)],
            'tingkat' => ['required', 'string', 'max:10'],
            'guru_pembimbing_id' => ['nullable', 'exists:gurus,id'],
            'kapasitas' => ['nullable', 'integer', 'min:1'],
            'ruangan' => ['nullable', 'string', 'max:100'],
            'fase' => ['nullable', 'string', 'max:5'],
        ]);
        $kelas->update($validated);

        return redirect()->route('kelas.index')->with('success', 'Kelas berhasil diupdate.');
    }

    public function destroy($id)
    {
        try {
            $kelas = Kelas::find($id);
            if (! $kelas) {
                return redirect()->route('kelas.index')->with('error', 'Kelas tidak ditemukan.');
            }

            // Cek apakah kelas memiliki jadwal
            $jadwalCount = DB::table('jadwals')->where('kelas_id', $id)->count();
            if ($jadwalCount > 0) {
                return redirect()->route('kelas.index')->with('error', "Kelas masih memiliki {$jadwalCount} jadwal. Hapus jadwal terlebih dahulu.");
            }

            // Hapus langsung via query builder
            $deleted = DB::table('kelas')->where('id', $id)->delete();

            if ($deleted) {
                return redirect()->route('kelas.index')->with('success', 'Kelas berhasil dihapus.');
            } else {
                return redirect()->route('kelas.index')->with('error', 'Gagal menghapus kelas. Data tidak ditemukan.');
            }
        } catch (\Exception $e) {
            \Log::error('Hapus kelas gagal: '.$e->getMessage());

            return redirect()->route('kelas.index')->with('error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }
}
