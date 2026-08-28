<?php

namespace App\Http\Controllers;

use App\Models\JamPelajaran;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JamPelajaranController extends Controller
{
    private array $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    public function index(Request $request): Response
    {
        $jamPelajaran = JamPelajaran::query()
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('hari', 'like', "%{$search}%")
                        ->orWhere('sesi_ke', $search);
                });
            })
            ->when(
                in_array($request->input('sort'), ['hari', 'sesi_ke', 'jam_mulai', 'jam_selesai'], true),
                fn ($query) => $query->orderBy($request->input('sort'), $request->input('direction') === 'desc' ? 'desc' : 'asc'),
                fn ($query) => $query->orderByRaw("field(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu')")->orderBy('sesi_ke')
            )
            ->paginate(20)
            ->withQueryString()
            ->through(fn (JamPelajaran $jam) => [
                'id' => $jam->id,
                'hari' => $jam->hari,
                'sesi_ke' => $jam->sesi_ke,
                'jam_mulai' => substr((string) $jam->jam_mulai, 0, 5),
                'jam_selesai' => substr((string) $jam->jam_selesai, 0, 5),
                'durasi_menit' => \Carbon\Carbon::parse($jam->jam_mulai)->diffInMinutes(\Carbon\Carbon::parse($jam->jam_selesai)),
            ]);

        $hariCount = JamPelajaran::selectRaw('hari, count(*) as total')
            ->groupBy('hari')
            ->pluck('total', 'hari');

        return Inertia::render('JamPelajaran/Index', [
            'jamPelajaran' => $jamPelajaran,
            'filters' => [
                'search' => $request->search,
                'sort' => $request->sort,
                'direction' => $request->direction,
            ],
            'summary' => [
                'total_sesi' => JamPelajaran::count(),
                'senin' => $hariCount['Senin'] ?? 0,
                'jumat' => $hariCount['Jumat'] ?? 0,
                'rata_rata' => round(JamPelajaran::count() / 5),
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('JamPelajaran/Form', [
            'hariList' => $this->hariList,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hari' => ['required', 'in:'.implode(',', $this->hariList)],
            'sesi_ke' => ['required', 'integer', 'min:1'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);
        JamPelajaran::create($validated);

        return redirect()->route('jam-pelajaran.index')->with('success', 'Jam pelajaran berhasil ditambahkan.');
    }

    public function edit(JamPelajaran $jamPelajaran): Response
    {
        return Inertia::render('JamPelajaran/Form', [
            'jamPelajaran' => [
                'id' => $jamPelajaran->id,
                'hari' => $jamPelajaran->hari,
                'sesi_ke' => $jamPelajaran->sesi_ke,
                'jam_mulai' => substr((string) $jamPelajaran->jam_mulai, 0, 5),
                'jam_selesai' => substr((string) $jamPelajaran->jam_selesai, 0, 5),
            ],
            'hariList' => $this->hariList,
        ]);
    }

    public function update(Request $request, JamPelajaran $jamPelajaran)
    {
        $validated = $request->validate([
            'hari' => ['required', 'in:'.implode(',', $this->hariList)],
            'sesi_ke' => ['required', 'integer', 'min:1'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);
        $jamPelajaran->update($validated);

        return redirect()->route('jam-pelajaran.index')->with('success', 'Jam pelajaran berhasil diupdate.');
    }

    public function destroy(JamPelajaran $jamPelajaran)
    {
        $jamPelajaran->delete();

        return redirect()->route('jam-pelajaran.index')->with('success', 'Jam pelajaran berhasil dihapus.');
    }
}
