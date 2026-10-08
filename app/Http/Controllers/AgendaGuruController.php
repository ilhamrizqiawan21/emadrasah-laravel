<?php

namespace App\Http\Controllers;

use App\Models\AgendaGuru;
use App\Models\Guru;
use App\Models\GuruPengganti;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AgendaGuruController extends Controller
{
    /**
     * Mapping hari Inggris → Indonesia (dipakai konsisten di seluruh controller ini).
     * Sebelumnya method pengganti() pakai $hariMap manual sedangkan
     * storePengganti() pakai translatedFormat('l') yang bergantung pada
     * locale Carbon — kalau locale belum di-set ke 'id', hasilnya tetap
     * bahasa Inggris dan query ke jam_pelajaran selalu kosong.
     */
    private array $hariMap = [
        'Monday' => 'Senin',
        'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday' => 'Kamis',
        'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
        'Sunday' => 'Minggu',
    ];

    /** Konversi objek Carbon → nama hari Indonesia. */
    private function hariIndo(Carbon $tanggal): string
    {
        return $this->hariMap[$tanggal->format('l')];
    }

    /**
     * ID guru milik user yang login, jika role-nya guru. Admin/operator → null
     * (boleh mengelola semua). Guru tanpa data guru terkait → 403.
     */
    private function guruIdTerbatas(): ?int
    {
        $user = auth()->user();
        if ($user->role !== 'guru') {
            return null;
        }
        $id = Guru::where('user_id', $user->id)->value('id');
        abort_if($id === null, 403, 'Akun Anda belum terhubung dengan data guru.');

        return (int) $id;
    }

    // -------------------------------------------------------------------------

    public function index()
    {
        request()->validate(['tanggal' => 'nullable|date']);
        $tanggal = request('tanggal') ? Carbon::parse(request('tanggal')) : today();
        $gurus = Guru::orderBy('nama')->get();

        $existingAgendas = AgendaGuru::whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('guru_id');

        $agendas = [];
        foreach ($gurus as $guru) {
            $agendas[$guru->id] = $existingAgendas[$guru->id] ?? null;
        }

        return view('absensi.index', compact('tanggal', 'gurus', 'agendas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'status' => 'required|array',
            'status.*' => 'in:hadir,izin,sakit,alpha',
            'keterangan' => 'nullable|array',
            'keterangan.*' => 'nullable|string|max:255',
        ]);

        // Hanya ID guru yang benar-benar ada; guru biasa hanya boleh mengisi dirinya sendiri.
        $guruIds = Guru::whereIn('id', array_keys($request->status))->pluck('id')->all();
        if (($sendiri = $this->guruIdTerbatas()) !== null) {
            $guruIds = array_intersect($guruIds, [$sendiri]);
        }

        DB::transaction(function () use ($request, $guruIds) {
            foreach ($request->status as $guruId => $status) {
                if (! in_array((int) $guruId, array_map('intval', $guruIds), true)) {
                    continue;
                }
                // Kunci berupa tanggal Carbon: string 'Y-m-d' tidak cocok dengan nilai tersimpan
                // di database yang menyimpan datetime (SQLite), sehingga simpan kedua kali gagal.
                AgendaGuru::updateOrCreate(
                    ['tanggal' => Carbon::parse($request->tanggal)->startOfDay(), 'guru_id' => $guruId],
                    [
                        'status' => $status,
                        'keterangan' => $request->keterangan[$guruId] ?? null,
                    ]
                );
            }
        });

        return redirect()
            ->route('absensi.index', ['tanggal' => $request->tanggal])
            ->with('success', 'Absensi berhasil disimpan.');
    }

    private function pastikanBolehKelola(AgendaGuru $agenda): void
    {
        $sendiri = $this->guruIdTerbatas();
        abort_if($sendiri !== null && (int) $agenda->guru_id !== $sendiri, 403, 'Anda hanya dapat mengelola absensi Anda sendiri.');
    }

    // -------------------------------------------------------------------------

    public function pengganti(AgendaGuru $agenda)
    {
        $this->pastikanBolehKelola($agenda);
        $tanggal = $agenda->tanggal;
        $hariIndo = $this->hariIndo($tanggal); // ← pakai helper, konsisten

        $jamList = JamPelajaran::where('hari', $hariIndo)->orderBy('sesi_ke')->get();
        $guruPenggantiOptions = Guru::where('id', '!=', $agenda->guru_id)->orderBy('nama')->get();

        $jadwalGuru = Jadwal::where('hari', $hariIndo)
            ->get(['guru_id', 'jam_mulai', 'jam_selesai'])
            ->groupBy('guru_id');

        return view('absensi.pengganti', compact('agenda', 'jamList', 'guruPenggantiOptions', 'jadwalGuru'));
    }

    public function storePengganti(Request $request, AgendaGuru $agenda)
    {
        $this->pastikanBolehKelola($agenda);
        $request->validate([
            'jam_pelajaran_id' => 'required|exists:jam_pelajaran,id',
            'guru_pengganti_id' => 'required|exists:gurus,id',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $jam = JamPelajaran::find($request->jam_pelajaran_id);
        $tanggal = $agenda->tanggal;

        // FIX POIN 1 — gunakan hariMap manual, bukan translatedFormat('l')
        // yang bergantung pada locale Carbon (bisa menghasilkan nama hari
        // bahasa Inggris jika locale belum di-set ke 'id').
        $hariIndo = $this->hariIndo($tanggal);

        // 1. Cek konflik jadwal tetap guru pengganti di hari & jam yang sama
        $jadwalConflict = Jadwal::where('guru_id', $request->guru_pengganti_id)
            ->where('hari', $hariIndo)
            ->where('jam_mulai', '<', $jam->jam_selesai)
            ->where('jam_selesai', '>', $jam->jam_mulai)
            ->exists();

        if ($jadwalConflict) {
            return back()
                ->withErrors(['guru_pengganti_id' => 'Guru pengganti sudah memiliki jadwal mengajar pada hari dan jam yang sama.'])
                ->withInput();
        }

        // 2. Cek apakah guru pengganti sudah ditugaskan di sesi yang sama pada hari ini
        $existingPengganti = GuruPengganti::whereHas('agendaGuru', function ($q) use ($tanggal) {
            $q->whereDate('tanggal', $tanggal);
        })
            ->where('jam_pelajaran_id', $request->jam_pelajaran_id)
            ->where('guru_pengganti_id', $request->guru_pengganti_id)
            ->exists();

        if ($existingPengganti) {
            return back()
                ->withErrors(['guru_pengganti_id' => 'Guru pengganti sudah ditugaskan di sesi ini.'])
                ->withInput();
        }

        // 3. Simpan pengganti
        GuruPengganti::create([
            'agenda_guru_id' => $agenda->id,
            'jam_pelajaran_id' => $request->jam_pelajaran_id,
            'guru_pengganti_id' => $request->guru_pengganti_id,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()
            ->route('absensi.index', ['tanggal' => $agenda->tanggal->format('Y-m-d')])
            ->with('success', 'Guru pengganti berhasil ditugaskan.');
    }

    // -------------------------------------------------------------------------

    public function rekap(Request $request)
    {
        $request->validate(['bulan' => 'nullable|integer|between:1,12', 'tahun' => 'nullable|integer|between:2000,2100']);
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $rekap = AgendaGuru::with('guru')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get()
            ->groupBy('guru_id')
            ->map(function ($items) {
                $hadir = $items->where('status', 'hadir')->count();
                $izin = $items->where('status', 'izin')->count();
                $sakit = $items->where('status', 'sakit')->count();
                $alpha = $items->where('status', 'alpha')->count();
                $guru = $items->first()->guru; // ambil relasi guru dari item pertama

                return [
                    'guru' => $guru,
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpha' => $alpha,
                ];
            });

        return view('absensi.rekap', compact('rekap', 'bulan', 'tahun'));
    }

    public function exportPdf(Request $request)
    {
        $request->validate(['bulan' => 'nullable|integer|between:1,12', 'tahun' => 'nullable|integer|between:2000,2100']);
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $rekap = AgendaGuru::with('guru')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get()
            ->groupBy('guru_id')
            ->map(function ($items) {
                $hadir = $items->where('status', 'hadir')->count();
                $izin = $items->where('status', 'izin')->count();
                $sakit = $items->where('status', 'sakit')->count();
                $alpha = $items->where('status', 'alpha')->count();
                $guru = $items->first()->guru;

                return [
                    'guru' => $guru,
                    'hadir' => $hadir,
                    'izin' => $izin,
                    'sakit' => $sakit,
                    'alpha' => $alpha,
                ];
            });

        $pdf = Pdf::loadView('absensi.rekap-pdf', compact('rekap', 'bulan', 'tahun'))
            ->setPaper('a4', 'landscape');

        return $pdf->stream('Rekap_Absensi_'.$bulan.'_'.$tahun.'.pdf');
    }
}
