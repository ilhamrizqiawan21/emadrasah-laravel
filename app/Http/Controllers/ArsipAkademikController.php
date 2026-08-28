<?php

namespace App\Http\Controllers;

use App\Models\ArsipAkademik;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

class ArsipAkademikController extends Controller
{
    public function index(Request $request)
    {
        $sorts = [
            'nama' => 'nama_arsip',
            'tipe' => 'tipe',
            'kelas' => 'kelas_id',
            'tahun' => 'tahun_pelajaran_id',
            'tanggal' => 'created_at',
        ];
        $sort = $sorts[$request->input('sort')] ?? 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $arsip = ArsipAkademik::with(['kelas', 'tahunPelajaran'])
            ->when($request->search, fn ($query, string $search) => $query->where('nama_arsip', 'like', "%{$search}%"))
            ->when($request->kelas_id, fn ($query, string $kelasId) => $query->where('kelas_id', $kelasId))
            ->when($request->tahun_pelajaran_id, fn ($query, string $tahunPelajaranId) => $query->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->when($request->semester, fn ($query, string $semester) => $query->where('semester', $semester))
            ->when($request->tipe, fn ($query, string $tipe) => $query->where('tipe', $tipe))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString();
        $kelas = Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get();
        $tahunPelajaran = TahunPelajaran::orderBy('kode')->get();

        return view('arsip-akademik.index', compact('arsip', 'kelas', 'tahunPelajaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required',
            'kelas_id' => 'required',
            'semester' => 'required|in:1,2',
            'nama_arsip' => 'required',
            'file_arsip' => 'required|file|mimes:pdf,xlsx,xls,zip|max:5120',
            'tipe' => 'required|in:Leger,RDM,Lainnya',
        ]);

        if ($request->hasFile('file_arsip')) {
            $file = $request->file('file_arsip');
            $filename = time().'_'.$file->getClientOriginalName();
            $path = $file->storeAs('arsip-akademik', $filename, 'public');

            ArsipAkademik::create([
                'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                'kelas_id' => $request->kelas_id,
                'semester' => $request->semester,
                'nama_arsip' => $request->nama_arsip,
                'file_path' => $path,
                'tipe' => $request->tipe,
            ]);
        }

        return redirect()->back()->with('success', 'Arsip akademik berhasil disimpan.');
    }

    public function destroy(ArsipAkademik $arsipAkademik)
    {
        $arsipAkademik->delete();

        return redirect()->back()->with('success', 'Arsip berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:arsip_akademik,id'],
        ]);

        ArsipAkademik::whereKey($validated['ids'])->delete();

        return redirect()
            ->route('arsip-akademik.index')
            ->with('success', count($validated['ids']).' arsip berhasil dihapus.');
    }
}
