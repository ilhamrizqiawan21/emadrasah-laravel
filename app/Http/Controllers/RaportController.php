<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\RaportNilai;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class RaportController extends Controller
{
    public function index(Request $request): Response
    {
        $sorts = [
            'nis' => 'nis',
            'nama_lengkap' => 'nama_lengkap',
            'kelas' => 'kelas_id',
            'status' => 'status',
        ];
        $sort = $sorts[$request->input('sort')] ?? 'nama_lengkap';
        $direction = $request->input('direction') === 'desc' ? 'desc' : 'asc';

        $siswa = Siswa::with(['kelas'])
            ->withCount('raportNilai')
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($query, string $status) => $query->where('status', $status))
            ->when($request->kelas_id, fn ($query, string $kelasId) => $query->where('kelas_id', $kelasId))
            ->orderBy($sort, $direction)
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Siswa $siswa) => [
                'id' => $siswa->id,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'nama_lengkap' => $siswa->nama_lengkap,
                'status' => $siswa->status,
                'raport_nilai_count' => $siswa->raport_nilai_count,
                'kelas' => $siswa->kelas ? [
                    'id' => $siswa->kelas->id,
                    'nama_kelas' => $siswa->kelas->nama_kelas,
                ] : null,
            ]);

        return Inertia::render('Raport/Index', [
            'siswa' => $siswa,
            'kelas' => Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get(['id', 'nama_kelas']),
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'kelas_id' => $request->kelas_id,
                'sort' => $request->sort,
                'direction' => $request->direction,
            ],
        ]);
    }

    public function manage(Siswa $siswa, Request $request): Response
    {
        $siswa->load('kelas');
        $tp = TahunPelajaran::orderBy('kode')->get(['id', 'kode', 'is_aktif']);
        $selectedTp = $request->tahun_pelajaran_id ?? (TahunPelajaran::where('is_aktif', true)->first()->id ?? null);
        $semester = $request->semester ?? 1;

        $mapels = Mapel::orderBy('urut')->orderBy('nama_mapel')->get(['id', 'nama_mapel', 'jp_per_sesi', 'parent_id', 'urut']);
        $nilai = RaportNilai::where('siswa_id', $siswa->id)
            ->where('tahun_pelajaran_id', $selectedTp)
            ->where('semester', $semester)
            ->get()
            ->keyBy('mapel_id');

        return Inertia::render('Raport/Manage', [
            'siswa' => [
                'id' => $siswa->id,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'nama_lengkap' => $siswa->nama_lengkap,
                'status' => $siswa->status,
                'kelas' => $siswa->kelas ? [
                    'id' => $siswa->kelas->id,
                    'nama_kelas' => $siswa->kelas->nama_kelas,
                ] : null,
            ],
            'tahunPelajaran' => $tp,
            'selectedTahunPelajaranId' => $selectedTp,
            'semester' => (int) $semester,
            'mapels' => $mapels,
            'nilai' => $nilai->map(fn (RaportNilai $nilai) => [
                'id' => $nilai->id,
                'mapel_id' => $nilai->mapel_id,
                'nilai_akhir' => $nilai->nilai_akhir,
                'deskripsi' => $nilai->deskripsi,
            ])->values(),
        ]);
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

        $tahunLabel = Str::of((string) ($tahunPelajaran?->kode ?? $selectedTp))
            ->replace(['/', '\\'], '-');

        return $pdf->stream('Raport_'.$siswa->nis.'_'.$tahunLabel.'_S'.$semester.'.pdf');
    }

    public function store(Request $request, Siswa $siswa)
    {
        $request->validate([
            'tahun_pelajaran_id' => ['required', 'exists:tahun_pelajaran,id'],
            'semester' => ['required', 'integer', 'in:1,2'],
            'nilai' => 'required|array',
            'nilai.*.angka' => ['nullable', 'integer', 'min:0', 'max:100'],
            'nilai.*.capaian' => ['nullable', 'string'],
        ]);

        foreach ($request->nilai as $mapelId => $data) {
            if (($data['angka'] ?? '') !== '') {
                RaportNilai::updateOrCreate(
                    [
                        'siswa_id' => $siswa->id,
                        'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                        'semester' => $request->semester,
                        'mapel_id' => $mapelId,
                    ],
                    [
                        'nilai_akhir' => $data['angka'],
                        'deskripsi' => $data['capaian'] ?? null,
                        'updated_by' => $request->user()?->id,
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Arsip nilai berhasil disimpan.');
    }
}
