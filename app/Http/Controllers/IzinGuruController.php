<?php

namespace App\Http\Controllers;

use App\Models\AgendaGuru;
use App\Models\IzinGuru;
use App\Models\User;
use App\Notifications\Pengingat;
use App\Support\AksesKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Guru mengajukan izin/sakit/cuti/dinas; admin dan operator memutuskan. Persetujuan mengisi
 * absensi guru otomatis (hari kerja, tanpa menimpa catatan hadir).
 */
class IzinGuruController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['status' => 'nullable|in:menunggu,disetujui,ditolak']);
        $guruId = AksesKelas::guruId($request->user());

        $izin = IzinGuru::with('guru')
            ->when($guruId, fn ($q) => $q->where('guru_id', $guruId))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('tanggal_mulai')->orderByDesc('id')->paginate(15)->withQueryString();

        return view('izin-guru.index', ['izin' => $izin, 'bolehMengajukan' => $guruId !== null]);
    }

    public function create(Request $request)
    {
        if (AksesKelas::guruId($request->user()) === null) {
            return redirect()->route('izin-guru.index')->with('error', 'Pengajuan izin dibuat oleh guru dari akunnya sendiri.');
        }

        return view('izin-guru.create');
    }

    public function store(Request $request)
    {
        $guruId = AksesKelas::guruId($request->user());
        abort_if($guruId === null, 403, 'Hanya guru yang dapat mengajukan izin.');

        $data = $request->validate([
            'jenis' => 'required|in:'.implode(',', array_keys(IzinGuru::JENIS)),
            'tanggal_mulai' => 'required|date|after_or_equal:'.today()->subDays(7)->toDateString(),
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai|before_or_equal:'.Carbon::parse($request->tanggal_mulai ?: today())->addDays(IzinGuru::MAKS_HARI)->toDateString(),
            'alasan' => 'required|string|max:500',
        ]);

        $bentrok = IzinGuru::where('guru_id', $guruId)->whereIn('status', ['menunggu', 'disetujui'])
            ->whereDate('tanggal_mulai', '<=', $data['tanggal_selesai'])->whereDate('tanggal_selesai', '>=', $data['tanggal_mulai'])->exists();
        if ($bentrok) {
            return back()->withInput()->withErrors(['tanggal_mulai' => 'Tanggal ini bertumpuk dengan pengajuan Anda yang masih berlaku.']);
        }

        $izin = IzinGuru::create($data + ['guru_id' => $guruId, 'status' => 'menunggu']);
        $izin->load('guru');

        $this->kabari(
            User::whereIn('role', ['admin', 'operator'])->where('is_active', true)->get(),
            'Pengajuan izin guru baru',
            "{$izin->guru->nama} mengajukan ".strtolower(IzinGuru::JENIS[$izin->jenis])." {$izin->rentang()}.",
            "izin:{$izin->id}:diajukan",
        );

        return redirect()->route('izin-guru.index')->with('success', 'Pengajuan terkirim dan menunggu persetujuan.');
    }

    public function destroy(Request $request, IzinGuru $izin)
    {
        $guruId = AksesKelas::guruId($request->user());
        abort_if($guruId === null, 403);
        abort_unless($izin->guru_id === $guruId, 404);

        if ($izin->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan yang sudah diputuskan tidak dapat dibatalkan.');
        }

        $izin->delete();

        return redirect()->route('izin-guru.index')->with('success', 'Pengajuan dibatalkan.');
    }

    /** Keputusan admin/operator; disetujui → absensi guru diisi untuk hari kerja dalam rentang. */
    public function putuskan(Request $request, IzinGuru $izin)
    {
        $data = $request->validate(['keputusan' => 'required|in:setuju,tolak', 'catatan' => 'nullable|string|max:255']);

        if ($izin->status !== 'menunggu') {
            return back()->with('error', 'Pengajuan ini sudah diputuskan.');
        }

        $setuju = $data['keputusan'] === 'setuju';

        DB::transaction(function () use ($izin, $data, $setuju, $request) {
            $izin->update([
                'status' => $setuju ? 'disetujui' : 'ditolak',
                'diputuskan_oleh' => $request->user()->id,
                'diputuskan_pada' => now(),
                'catatan_keputusan' => $data['catatan'] ?? null,
            ]);

            if ($setuju) {
                $this->isiAbsensi($izin);
            }
        });

        $izin->load('guru');
        $this->kabari(
            User::where('id', $izin->guru->user_id)->where('is_active', true)->get(),
            $setuju ? 'Pengajuan izin disetujui' : 'Pengajuan izin ditolak',
            'Pengajuan '.strtolower(IzinGuru::JENIS[$izin->jenis])." {$izin->rentang()} ".($setuju ? 'disetujui' : 'ditolak').($izin->catatan_keputusan ? ": {$izin->catatan_keputusan}" : '.'),
            "izin:{$izin->id}:keputusan",
        );

        return back()->with('success', $setuju ? 'Pengajuan disetujui dan absensi guru diperbarui.' : 'Pengajuan ditolak.');
    }

    /** Hari Minggu dilewati; catatan selain alpha (hadir, izin, sakit) tidak ditimpa. */
    private function isiAbsensi(IzinGuru $izin): void
    {
        $keterangan = IzinGuru::JENIS[$izin->jenis].' (disetujui)';

        for ($hari = $izin->tanggal_mulai->copy(); $hari->lte($izin->tanggal_selesai); $hari->addDay()) {
            if ($hari->isSunday()) {
                continue;
            }

            $ada = AgendaGuru::where('guru_id', $izin->guru_id)->whereDate('tanggal', $hari)->first();
            if ($ada && $ada->status !== 'alpha') {
                continue;
            }

            $ada
                ? $ada->update(['status' => $izin->statusAbsensi(), 'keterangan' => $keterangan])
                : AgendaGuru::create(['tanggal' => $hari->toDateString(), 'guru_id' => $izin->guru_id, 'status' => $izin->statusAbsensi(), 'keterangan' => $keterangan]);
        }
    }

    /** Gagal kirim notifikasi tidak boleh menggagalkan proses utama. */
    private function kabari($penerima, string $judul, string $pesan, string $kunci): void
    {
        foreach ($penerima as $user) {
            try {
                $user->notify(new Pengingat($judul, $pesan, route('izin-guru.index'), $kunci));
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
