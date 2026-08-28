<?php

namespace App\Http\Controllers;

use App\Models\AgendaGuru;
use App\Models\Guru;
use App\Models\GuruPengganti;
use App\Models\Jadwal;
use App\Models\JamPelajaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

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
        'Monday'    => 'Senin',
        'Tuesday'   => 'Selasa',
        'Wednesday' => 'Rabu',
        'Thursday'  => 'Kamis',
        'Friday'    => 'Jumat',
        'Saturday'  => 'Sabtu',
        'Sunday'    => 'Minggu',
    ];

    /** Konversi objek Carbon → nama hari Indonesia. */
    private function hariIndo(Carbon $tanggal): string
    {
        return $this->hariMap[$tanggal->format('l')];
    }

    // -------------------------------------------------------------------------

    public function index(Request $request): Response
    {
        $tanggal = $request->tanggal ? Carbon::parse($request->tanggal) : today();
        $gurus   = Guru::orderBy('nama')->get();

        $existingAgendas = AgendaGuru::with(['guruPengganti.guruPengganti'])
            ->whereDate('tanggal', $tanggal)
            ->get()
            ->keyBy('guru_id');

        $rows = $gurus->map(function (Guru $guru) use ($existingAgendas) {
            $agenda = $existingAgendas[$guru->id] ?? null;
            $pengganti = $agenda?->guruPengganti->first();

            return [
                'guru' => [
                    'id' => $guru->id,
                    'kode' => $guru->kode,
                    'nama' => $guru->nama,
                    'bidang_studi' => $guru->bidang_studi,
                ],
                'agenda' => $agenda ? [
                    'id' => $agenda->id,
                    'status' => $agenda->status,
                    'keterangan' => $agenda->keterangan,
                    'pengganti' => $pengganti ? [
                        'id' => $pengganti->id,
                        'guru_nama' => $pengganti->guruPengganti?->nama,
                    ] : null,
                ] : null,
            ];
        });

        $summary = [
            'total_guru' => $rows->count(),
            'hadir' => $rows->filter(fn ($row) => ($row['agenda']['status'] ?? 'hadir') === 'hadir')->count(),
            'izin' => $rows->filter(fn ($row) => ($row['agenda']['status'] ?? 'hadir') === 'izin')->count(),
            'sakit' => $rows->filter(fn ($row) => ($row['agenda']['status'] ?? 'hadir') === 'sakit')->count(),
            'alpha' => $rows->filter(fn ($row) => ($row['agenda']['status'] ?? 'hadir') === 'alpha')->count(),
        ];

        return Inertia::render('Absensi/Index', [
            'tanggal' => $tanggal->format('Y-m-d'),
            'rows' => $rows,
            'summary' => $summary,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'       => 'required|date',
            'status'        => 'required|array',
            'status.*'      => 'in:hadir,izin,sakit,alpha',
            'keterangan'    => 'nullable|array',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->status as $guruId => $status) {
                AgendaGuru::updateOrCreate(
                    ['tanggal' => $request->tanggal, 'guru_id' => $guruId],
                    [
                        'status'      => $status,
                        'keterangan'  => $request->keterangan[$guruId] ?? null,
                    ]
                );
            }
        });

        return redirect()
            ->route('absensi.index', ['tanggal' => $request->tanggal])
            ->with('success', 'Absensi berhasil disimpan.');
    }

    // -------------------------------------------------------------------------

    public function pengganti(AgendaGuru $agenda): Response
    {
        $agenda->load('guru');
        $tanggal  = $agenda->tanggal;
        $hariIndo = $this->hariIndo($tanggal); // ← pakai helper, konsisten

        $jamList               = JamPelajaran::where('hari', $hariIndo)->orderBy('sesi_ke')->get();
        $guruPenggantiOptions  = Guru::where('id', '!=', $agenda->guru_id)->orderBy('nama')->get();

        $jadwalGuru = Jadwal::where('hari', $hariIndo)
            ->get(['guru_id', 'jam_mulai', 'jam_selesai'])
            ->groupBy('guru_id');

        return Inertia::render('Absensi/Pengganti', [
            'agenda' => [
                'id' => $agenda->id,
                'tanggal' => $agenda->tanggal->format('Y-m-d'),
                'status' => $agenda->status,
                'keterangan' => $agenda->keterangan,
                'guru' => [
                    'id' => $agenda->guru->id,
                    'kode' => $agenda->guru->kode,
                    'nama' => $agenda->guru->nama,
                ],
            ],
            'jamList' => $jamList->map(fn (JamPelajaran $jam) => [
                'id' => $jam->id,
                'hari' => $jam->hari,
                'sesi_ke' => $jam->sesi_ke,
                'jam_mulai' => substr((string) $jam->jam_mulai, 0, 5),
                'jam_selesai' => substr((string) $jam->jam_selesai, 0, 5),
            ]),
            'guruPenggantiOptions' => $guruPenggantiOptions->map(fn (Guru $guru) => [
                'id' => $guru->id,
                'kode' => $guru->kode,
                'nama' => $guru->nama,
                'bidang_studi' => $guru->bidang_studi,
            ]),
            'jadwalGuru' => $jadwalGuru,
        ]);
    }

    public function storePengganti(Request $request, AgendaGuru $agenda)
    {
        $request->validate([
            'jam_pelajaran_id'   => 'required|exists:jam_pelajaran,id',
            'guru_pengganti_id'  => 'required|exists:gurus,id',
            'keterangan'         => 'nullable',
        ]);

        $jam      = JamPelajaran::find($request->jam_pelajaran_id);
        $tanggal  = $agenda->tanggal;

        // FIX POIN 1 — gunakan hariMap manual, bukan translatedFormat('l')
        // yang bergantung pada locale Carbon (bisa menghasilkan nama hari
        // bahasa Inggris jika locale belum di-set ke 'id').
        $hariIndo = $this->hariIndo($tanggal);

        // 1. Cek konflik jadwal tetap guru pengganti di hari & jam yang sama
        $jadwalConflict = Jadwal::where('guru_id', $request->guru_pengganti_id)
            ->where('hari', $hariIndo)
            ->where(function ($q) use ($jam) {
                // Overlap: jam mulai pengganti ada di antara jam sesi ini
                $q->whereBetween('jam_mulai', [$jam->jam_mulai, $jam->jam_selesai])
                  ->orWhereBetween('jam_selesai', [$jam->jam_mulai, $jam->jam_selesai])
                  ->orWhere(function ($q2) use ($jam) {
                      $q2->where('jam_mulai', '<=', $jam->jam_mulai)
                         ->where('jam_selesai', '>=', $jam->jam_selesai);
                  });
            })
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
            'agenda_guru_id'    => $agenda->id,
            'jam_pelajaran_id'  => $request->jam_pelajaran_id,
            'guru_pengganti_id' => $request->guru_pengganti_id,
            'keterangan'        => $request->keterangan,
        ]);

        return redirect()
            ->route('absensi.index', ['tanggal' => $agenda->tanggal->format('Y-m-d')])
            ->with('success', 'Guru pengganti berhasil ditugaskan.');
    }

    // -------------------------------------------------------------------------

    public function rekap(Request $request)
    {
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $rekap = AgendaGuru::with('guru')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get()
            ->groupBy('guru_id')
            ->map(function ($items) {
                $hadir  = $items->where('status', 'hadir')->count();
                $izin   = $items->where('status', 'izin')->count();
                $sakit  = $items->where('status', 'sakit')->count();
                $alpha  = $items->where('status', 'alpha')->count();
                $guru   = $items->first()->guru; // ambil relasi guru dari item pertama
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
        $bulan = $request->bulan ?? now()->month;
        $tahun = $request->tahun ?? now()->year;

        $rekap = AgendaGuru::with('guru')
            ->whereYear('tanggal', $tahun)
            ->whereMonth('tanggal', $bulan)
            ->get()
            ->groupBy('guru_id')
            ->map(function ($items) {
                $hadir  = $items->where('status', 'hadir')->count();
                $izin   = $items->where('status', 'izin')->count();
                $sakit  = $items->where('status', 'sakit')->count();
                $alpha  = $items->where('status', 'alpha')->count();
                $guru   = $items->first()->guru;
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

        return $pdf->stream('Rekap_Absensi_' . $bulan . '_' . $tahun . '.pdf');
    }
}
