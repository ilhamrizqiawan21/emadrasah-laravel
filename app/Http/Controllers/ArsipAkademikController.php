<?php

namespace App\Http\Controllers;

use App\Models\ArsipAkademik;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;

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
            'tahun_pelajaran_id' => 'required',
            'kelas_id' => 'required',
            'semester' => 'required|in:1,2',
            'nama_arsip' => 'required',
            'file_arsip' => 'required|file|mimes:pdf,xlsx,xls,zip|max:5120',
            'tipe' => 'required|in:Leger,RDM,Lainnya',
        ]);

        if ($request->hasFile('file_arsip')) {
            $file = $request->file('file_arsip');
            $filename = time() . '_' . $file->getClientOriginalName();
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
}
