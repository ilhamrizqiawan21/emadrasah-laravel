<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Mapel;
use App\Models\TahunPelajaran;
use App\Models\RaportNilai;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class RaportController extends Controller
{
    public function index(Request $request)
    {
        $siswa = Siswa::with(['kelas'])->when($request->search, function($q) use ($request) {
            $q->where('nama_lengkap', 'like', "%{$request->search}%")
              ->orWhere('nis', $request->search);
        })->paginate(15);
        
        return view('raport.index', compact('siswa'));
    }

    public function manage(Siswa $siswa, Request $request)
    {
        $tp = TahunPelajaran::all();
        $selectedTp = $request->tahun_pelajaran_id ?? (TahunPelajaran::where('is_aktif', true)->first()->id ?? null);
        $semester = $request->semester ?? 1;

        $mapels = Mapel::all();
        $nilai = RaportNilai::where('siswa_id', $siswa->id)
            ->where('tahun_pelajaran_id', $selectedTp)
            ->where('semester', $semester)
            ->get()
            ->keyBy('mapel_id');

        return view('raport.manage', compact('siswa', 'tp', 'selectedTp', 'semester', 'mapels', 'nilai'));
    }

    public function exportPdf(Siswa $siswa, Request $request)
    {
        $selectedTp = $request->tahun_pelajaran_id ?? (TahunPelajaran::where('is_aktif', true)->first()->id ?? null);
        $semester = $request->semester ?? 1;

        $mapels = Mapel::all();
        $nilai = RaportNilai::where('siswa_id', $siswa->id)
            ->where('tahun_pelajaran_id', $selectedTp)
            ->where('semester', $semester)
            ->get()
            ->keyBy('mapel_id');

        $tahunPelajaran = TahunPelajaran::find($selectedTp);

        $pdf = Pdf::loadView('raport.pdf', compact('siswa', 'mapels', 'nilai', 'tahunPelajaran', 'semester'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream('Raport_' . $siswa->nis . '_' . ($tahunPelajaran?->kode ?? $selectedTp) . '_S' . $semester . '.pdf');
    }

    public function store(Request $request, Siswa $siswa)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required',
            'semester' => 'required',
            'nilai' => 'required|array',
        ]);

        foreach ($request->nilai as $mapelId => $data) {
            if (isset($data['angka'])) {
                RaportNilai::updateOrCreate(
                    [
                        'siswa_id' => $siswa->id,
                        'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                        'semester' => $request->semester,
                        'mapel_id' => $mapelId
                    ],
                    [
                        'nilai_akhir' => $data['angka'],
                        'capaian_kompetensi' => $data['capaian'] ?? null
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Arsip nilai berhasil disimpan.');
    }
}
