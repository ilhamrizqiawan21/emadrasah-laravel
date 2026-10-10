<?php

namespace App\Http\Controllers;

use App\Models\KalenderAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Semua role melihat kalender akademik; admin dan operator mengelola agendanya. */
class KalenderController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['bulan' => ['nullable', 'date_format:Y-m']]);

        $bulan = Carbon::createFromFormat('!Y-m', $request->bulan ?: now()->format('Y-m'));
        $awal = $bulan->copy()->startOfWeek();
        $akhir = $bulan->copy()->endOfMonth()->endOfWeek();

        $agenda = KalenderAkademik::antara($awal, $akhir)->orderBy('tanggal_mulai')->orderBy('id')->get();

        $minggu = [];
        for ($urut = 0, $hari = $awal->copy(); $hari->lte($akhir); $hari->addDay(), $urut++) {
            $minggu[intdiv($urut, 7)][] = [
                'tanggal' => $hari->copy(),
                'bulanIni' => $hari->month === $bulan->month,
                'agenda' => $agenda->filter(fn ($a) => $a->tanggal_mulai->lte($hari) && $a->tanggal_selesai->gte($hari))->values(),
            ];
        }

        $daftarBulan = $agenda->filter(fn ($a) => $a->tanggal_mulai->lte($bulan->copy()->endOfMonth()) && $a->tanggal_selesai->gte($bulan));

        return view('kalender.index', [
            'bulan' => $bulan, 'minggu' => $minggu, 'daftarBulan' => $daftarBulan,
            'kelola' => in_array($request->user()->role, ['admin', 'operator'], true),
        ]);
    }

    public function create(Request $request)
    {
        return view('kalender.form', ['agenda' => new KalenderAkademik(['jenis' => 'kegiatan', 'tanggal_mulai' => $request->query('tanggal') ?: today(), 'tanggal_selesai' => $request->query('tanggal') ?: today()])]);
    }

    public function store(Request $request)
    {
        $agenda = KalenderAkademik::create($this->validasi($request));

        return redirect()->route('kalender.index', ['bulan' => $agenda->tanggal_mulai->format('Y-m')])->with('success', 'Agenda ditambahkan.');
    }

    public function edit(KalenderAkademik $agenda)
    {
        return view('kalender.form', compact('agenda'));
    }

    public function update(Request $request, KalenderAkademik $agenda)
    {
        $agenda->update($this->validasi($request));

        return redirect()->route('kalender.index', ['bulan' => $agenda->tanggal_mulai->format('Y-m')])->with('success', 'Agenda diperbarui.');
    }

    public function destroy(KalenderAkademik $agenda)
    {
        $agenda->delete();

        return redirect()->route('kalender.index', ['bulan' => $agenda->tanggal_mulai->format('Y-m')])->with('success', 'Agenda dihapus.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'judul' => 'required|string|max:150',
            'jenis' => 'required|in:'.implode(',', array_keys(KalenderAkademik::JENIS)),
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai|before_or_equal:'.Carbon::parse($request->tanggal_mulai ?: today())->addDays(366)->toDateString(),
            'keterangan' => 'nullable|string|max:255',
        ]);
    }
}
