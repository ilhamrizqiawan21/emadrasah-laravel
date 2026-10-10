<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\RaportCatatan;
use App\Models\RaportEkskul;
use App\Models\RaportKehadiran;
use App\Models\RaportNilai;
use App\Models\RaportRilis;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Support\DataRaport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RaportController extends Controller
{
    public function index(Request $request)
    {
        $siswa = Siswa::with(['kelas'])->when($request->search, function ($q) use ($request) {
            $q->where('nama_lengkap', 'like', "%{$request->search}%")
                ->orWhere('nis', $request->search);
        })->orderBy('nama_lengkap')->orderBy('id')->paginate(15);

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $tahunList = TahunPelajaran::orderByDesc('kode')->get();
        $tahunAktifId = TahunPelajaran::where('is_aktif', true)->value('id');

        $dirilis = RaportRilis::all()->map(fn ($r) => $r->tahun_pelajaran_id.'-'.$r->semester)->flip();

        return view('raport.index', compact('siswa', 'kelasList', 'tahunList', 'tahunAktifId', 'dirilis'));
    }

    public function manage(Siswa $siswa, Request $request)
    {
        $tp = TahunPelajaran::all();
        $selectedTp = $request->tahun_pelajaran_id ?? (TahunPelajaran::where('is_aktif', true)->first()->id ?? null);
        $semester = (int) ($request->semester ?? 1);

        $data = DataRaport::untuk($siswa, $selectedTp, $semester, $request->boolean('hitung'));

        return view('raport.manage', $data + compact('tp', 'selectedTp'));
    }

    public function exportPdf(Siswa $siswa, Request $request)
    {
        $selectedTp = $request->tahun_pelajaran_id ?? (TahunPelajaran::where('is_aktif', true)->first()->id ?? null);
        $semester = (int) ($request->semester ?? 1);

        $data = DataRaport::untuk($siswa, $selectedTp, $semester);
        $pdf = Pdf::loadView('raport.pdf', $data)->setPaper('a4', 'portrait');

        $tpKode = str_replace(['/', '\\'], '-', $data['tahunPelajaran']?->kode ?? (string) $selectedTp);

        return $pdf->stream('Raport_'.$siswa->nis.'_'.$tpKode.'_S'.$semester.'.pdf');
    }

    /** Raport seluruh siswa aktif satu kelas dalam satu PDF, satu siswa per halaman (urut nama). */
    public function exportKelas(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
            'tahun_pelajaran_id' => 'required|integer|exists:tahun_pelajaran,id',
            'semester' => 'required|in:1,2',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);
        $siswa = Siswa::with('kelas')->where('kelas_id', $kelas->id)->where('status', 'Aktif')->orderBy('nama_lengkap')->get();
        if ($siswa->isEmpty()) {
            return back()->with('error', 'Kelas ini belum memiliki siswa aktif.');
        }

        $semester = (int) $request->semester;
        $semuaRaport = $siswa->map(fn ($s) => DataRaport::untuk($s, (int) $request->tahun_pelajaran_id, $semester))->all();
        $pdf = Pdf::loadView('raport.pdf-kelas', compact('kelas', 'semuaRaport'))->setPaper('a4', 'portrait');

        $tpKode = str_replace(['/', '\\'], '-', $semuaRaport[0]['tahunPelajaran']?->kode ?? (string) $request->tahun_pelajaran_id);

        return $pdf->stream('Raport_'.Str::slug($kelas->nama_kelas).'_'.$tpKode.'_S'.$semester.'.pdf');
    }

    /** Ekskul, kehadiran, dan catatan wali kelas untuk satu siswa pada satu semester. */
    public function storePelengkap(Request $request, Siswa $siswa)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|integer|exists:tahun_pelajaran,id',
            'semester' => 'required|in:1,2',
            'ekskul' => 'nullable|array|max:8',
            'ekskul.*.nama' => 'nullable|string|max:150',
            'ekskul.*.nilai' => 'nullable|string|max:30',
            'ekskul.*.keterangan' => 'nullable|string|max:255',
            'sakit' => 'required|integer|min:0|max:366',
            'ijin' => 'required|integer|min:0|max:366',
            'tanpa_keterangan' => 'required|integer|min:0|max:366',
            'catatan_wali' => 'nullable|string|max:2000',
        ]);

        $kunci = ['siswa_id' => $siswa->id, 'tahun_pelajaran_id' => $request->tahun_pelajaran_id, 'semester' => $request->semester];

        DB::transaction(function () use ($request, $kunci) {
            RaportEkskul::where($kunci)->delete();
            $urut = 0;
            foreach ($request->input('ekskul', []) as $baris) {
                $nama = trim((string) ($baris['nama'] ?? ''));
                if ($nama === '') {
                    continue;
                }
                RaportEkskul::create($kunci + [
                    'nama_ekskul' => $nama,
                    'nilai' => $baris['nilai'] ?? null,
                    'keterangan' => $baris['keterangan'] ?? null,
                    'urut' => ++$urut,
                ]);
            }

            RaportKehadiran::updateOrCreate($kunci, $request->only('sakit', 'ijin', 'tanpa_keterangan'));
            RaportCatatan::updateOrCreate($kunci, ['catatan_wali' => $request->catatan_wali]);
        });

        return redirect()->back()->with('success', 'Ekskul, kehadiran, dan catatan wali berhasil disimpan.');
    }

    public function store(Request $request, Siswa $siswa)
    {
        $request->validate([
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester' => 'required|in:1,2',
            'nilai' => 'required|array',
            'nilai.*.angka' => 'nullable|numeric|between:0,100',
            'nilai.*.capaian' => 'nullable|string|max:1000',
        ]);

        $mapelIds = Mapel::pluck('id')->all();

        foreach ($request->nilai as $mapelId => $data) {
            if (! in_array((int) $mapelId, $mapelIds, true)) {
                continue; // abaikan mapel yang tidak ada (hindari error FK)
            }
            if (isset($data['angka']) && $data['angka'] !== '') {
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
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Arsip nilai berhasil disimpan.');
    }
}
