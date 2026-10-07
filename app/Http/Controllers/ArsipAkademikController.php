<?php

namespace App\Http\Controllers;

use App\Models\ArsipAkademik;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ArsipAkademikController extends Controller
{
    public function index()
    {
        $arsip = ArsipAkademik::with(['kelas', 'tahunPelajaran'])->latest()->paginate(15);
        $kelas = Kelas::all();
        $tahunPelajaran = TahunPelajaran::all();
        return view('arsip-akademik.index', compact('arsip', 'kelas', 'tahunPelajaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'kelas_id' => 'required|exists:kelas,id',
            'semester' => 'required|in:1,2',
            'nama_arsip' => 'required|string|max:255',
            'file_arsip' => 'required|file|mimes:pdf,xlsx,xls,zip|max:5120',
            'tipe' => 'required|in:Leger,RDM,Lainnya',
        ]);

        if ($request->hasFile('file_arsip')) {
            $path = $request->file('file_arsip')->store('arsip-akademik', 'local');

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
        if ($arsipAkademik->file_path) {
            Storage::disk('local')->delete($arsipAkademik->file_path);
        }
        $arsipAkademik->delete();
        return redirect()->back()->with('success', 'Arsip berhasil dihapus.');
    }
}
