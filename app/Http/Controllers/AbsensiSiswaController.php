<?php

namespace App\Http\Controllers;

use App\Models\AbsensiSiswa;
use App\Models\Kelas;
use App\Models\Siswa;
use App\Models\User;
use App\Notifications\SiswaAlpha;
use App\Support\AksesKelas;
use App\Support\RekapAbsensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiSiswaController extends Controller
{
    private const STATUS = RekapAbsensi::STATUS;

    private function kelasBoleh()
    {
        return AksesKelas::kelas(auth()->user());
    }

    /** Kelas terpilih dari request; 403 bila di luar hak akses user. */
    private function kelasTerpilih($kelasBoleh, $kelasId): ?Kelas
    {
        if (! $kelasId) {
            return $kelasBoleh->first();
        }

        $kelas = $kelasBoleh->firstWhere('id', (int) $kelasId);
        abort_if($kelas === null, 403, 'Anda tidak berhak mengelola absensi kelas ini.');

        return $kelas;
    }

    public function index(Request $request)
    {
        $request->validate(['tanggal' => 'nullable|date', 'kelas_id' => 'nullable|integer']);

        $kelasList = $this->kelasBoleh();
        $kelas = $this->kelasTerpilih($kelasList, $request->kelas_id);
        $tanggal = $request->tanggal ? Carbon::parse($request->tanggal) : today();

        $siswa = collect();
        $absensi = collect();
        if ($kelas) {
            $siswa = Siswa::where('kelas_id', $kelas->id)->where('status', 'Aktif')->orderBy('nama_lengkap')->get();
            $absensi = AbsensiSiswa::whereDate('tanggal', $tanggal)->whereIn('siswa_id', $siswa->pluck('id'))->get()->keyBy('siswa_id');
        }

        return view('absensi-siswa.index', compact('kelasList', 'kelas', 'tanggal', 'siswa', 'absensi'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|integer|exists:kelas,id',
            'tanggal' => 'required|date|before_or_equal:today',
            'status' => 'required|array',
            'status.*' => 'in:'.implode(',', self::STATUS),
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:255',
        ]);

        $kelas = $this->kelasTerpilih($this->kelasBoleh(), $request->kelas_id);

        // Hanya siswa aktif di kelas ini; id lain diabaikan diam-diam.
        $siswaIds = Siswa::where('kelas_id', $kelas->id)->where('status', 'Aktif')
            ->whereIn('id', array_keys($request->status))->pluck('id');
        $tanggal = Carbon::parse($request->tanggal)->startOfDay();
        $sebelumnya = AbsensiSiswa::whereDate('tanggal', $tanggal)->whereIn('siswa_id', $siswaIds)->pluck('status', 'siswa_id');

        DB::transaction(function () use ($request, $siswaIds, $kelas, $tanggal) {
            foreach ($siswaIds as $siswaId) {
                AbsensiSiswa::updateOrCreate(
                    ['tanggal' => $tanggal, 'siswa_id' => $siswaId],
                    [
                        'kelas_id' => $kelas->id,
                        'status' => $request->status[$siswaId],
                        'keterangan' => $request->keterangan[$siswaId] ?? null,
                        'dicatat_oleh' => auth()->id(),
                    ]
                );
            }
        });

        $this->kabariWali($siswaIds, $sebelumnya, $request->status, $tanggal);

        return redirect()
            ->route('absensi-siswa.index', ['kelas_id' => $kelas->id, 'tanggal' => $request->tanggal])
            ->with('success', 'Absensi siswa berhasil disimpan.');
    }

    /**
     * Kabari wali siswa yang BARU menjadi alpha (bukan sudah alpha sebelumnya). Tanggal lebih lama dari
     * seminggu tidak dikabari agar pengisian susulan tidak membanjiri wali. Gagal kirim tidak boleh
     * menggagalkan penyimpanan absensi, cukup dicatat di log.
     */
    private function kabariWali($siswaIds, $sebelumnya, array $status, Carbon $tanggal): void
    {
        if ($tanggal->lt(today()->subDays(7))) {
            return;
        }

        $baruAlpha = $siswaIds->filter(fn ($id) => ($status[$id] ?? null) === 'alpha' && ($sebelumnya[$id] ?? null) !== 'alpha');
        if ($baruAlpha->isEmpty()) {
            return;
        }

        $wali = User::where('is_active', true)->whereHas('anak', fn ($q) => $q->whereIn('siswa.id', $baruAlpha))
            ->with(['anak' => fn ($q) => $q->whereIn('siswa.id', $baruAlpha)])->get();

        foreach ($wali as $user) {
            foreach ($user->anak as $anak) {
                try {
                    $user->notify(new SiswaAlpha($anak, $tanggal));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }
    }

    public function rekap(Request $request)
    {
        $request->validate(['bulan' => ['nullable', 'date_format:Y-m'], 'kelas_id' => 'nullable|integer']);

        $kelasList = $this->kelasBoleh();
        $kelas = $this->kelasTerpilih($kelasList, $request->kelas_id);
        $bulan = Carbon::createFromFormat('Y-m-d', ($request->bulan ?: now()->format('Y-m')).'-01')->startOfMonth();

        $siswa = collect();
        $rekap = [];
        if ($kelas) {
            ['siswa' => $siswa, 'rekap' => $rekap] = RekapAbsensi::untuk($kelas, $bulan);
        }

        return view('absensi-siswa.rekap', compact('kelasList', 'kelas', 'bulan', 'siswa', 'rekap'));
    }
}
