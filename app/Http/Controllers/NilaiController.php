<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\RaportNilai;
use App\Models\Siswa;
use App\Models\TahunPelajaran;
use App\Support\AksesKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NilaiController extends Controller
{
    /** Kelas terpilih (atau yang pertama); 403 bila di luar hak akses. */
    private function pilihKelas($kelasList, $kelasId): ?Kelas
    {
        if (! $kelasId) {
            return $kelasList->first();
        }

        $kelas = $kelasList->firstWhere('id', (int) $kelasId);
        abort_if($kelas === null, 403, 'Anda tidak berhak menilai kelas ini.');

        return $kelas;
    }

    private function pilihMapel($mapelList, $mapelId): ?Mapel
    {
        if (! $mapelId) {
            return $mapelList->first();
        }

        $mapel = $mapelList->firstWhere('id', (int) $mapelId);
        abort_if($mapel === null, 403, 'Anda tidak berhak menilai mata pelajaran ini di kelas tersebut.');

        return $mapel;
    }

    public function index(Request $request)
    {
        $request->validate([
            'kelas_id' => 'nullable|integer',
            'mapel_id' => 'nullable|integer',
            'tahun_pelajaran_id' => 'nullable|integer|exists:tahun_pelajaran,id',
            'semester' => 'nullable|in:1,2',
        ]);

        $user = auth()->user();
        $kelasList = AksesKelas::kelasMengajar($user);
        $kelas = $this->pilihKelas($kelasList, $request->kelas_id);
        $mapelList = $kelas ? AksesKelas::mapel($user, $kelas->id) : collect();
        $mapel = $this->pilihMapel($mapelList, $request->mapel_id);

        $tahunList = TahunPelajaran::orderByDesc('kode')->get();
        $tahunId = $request->tahun_pelajaran_id ?: TahunPelajaran::where('is_aktif', true)->value('id');
        $semester = (int) ($request->semester ?: 1);

        $siswa = collect();
        $nilai = collect();
        if ($kelas && $mapel && $tahunId) {
            $siswa = Siswa::where('kelas_id', $kelas->id)->where('status', 'Aktif')->orderBy('nama_lengkap')->get();
            $nilai = RaportNilai::where('mapel_id', $mapel->id)->where('tahun_pelajaran_id', $tahunId)
                ->where('semester', $semester)->whereIn('siswa_id', $siswa->pluck('id'))->get()->keyBy('siswa_id');
        }

        return view('nilai.index', compact('kelasList', 'kelas', 'mapelList', 'mapel', 'tahunList', 'tahunId', 'semester', 'siswa', 'nilai'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
            'mapel_id' => 'required|integer|exists:mapels,id',
            'tahun_pelajaran_id' => 'required|integer|exists:tahun_pelajaran,id',
            'semester' => 'required|in:1,2',
            'nilai' => 'required|array',
            'nilai.*.angka' => 'nullable|numeric|between:0,100',
            'nilai.*.capaian' => 'nullable|string|max:1000',
        ]);

        $user = auth()->user();
        $kelas = $this->pilihKelas(AksesKelas::kelasMengajar($user), $request->kelas_id);
        $mapel = $this->pilihMapel(AksesKelas::mapel($user, $kelas->id), $request->mapel_id);

        // Hanya siswa aktif di kelas ini; id lain diabaikan.
        $siswaIds = Siswa::where('kelas_id', $kelas->id)->where('status', 'Aktif')
            ->whereIn('id', array_keys($request->nilai))->pluck('id');

        DB::transaction(function () use ($request, $siswaIds, $mapel, $user) {
            foreach ($siswaIds as $siswaId) {
                $data = $request->nilai[$siswaId];
                // Kosong = dilewati (sama seperti raport per siswa); nilai lama tidak terhapus.
                if (! isset($data['angka']) || $data['angka'] === '') {
                    continue;
                }

                RaportNilai::updateOrCreate(
                    [
                        'siswa_id' => $siswaId,
                        'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                        'semester' => $request->semester,
                        'mapel_id' => $mapel->id,
                    ],
                    [
                        'nilai_akhir' => $data['angka'],
                        'deskripsi' => $data['capaian'] ?? null,
                        'updated_by' => $user->id,
                    ]
                );
            }
        });

        return redirect()
            ->route('nilai.index', $request->only('kelas_id', 'mapel_id', 'tahun_pelajaran_id', 'semester'))
            ->with('success', 'Nilai berhasil disimpan.');
    }
}
