<?php

namespace App\Http\Controllers;

use App\Models\Pengumuman;
use App\Models\User;
use App\Notifications\Pengingat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

/** Semua role membaca pengumuman yang ditujukan kepadanya; admin dan operator yang mengelola. */
class PengumumanController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->user()->role;
        $kelola = in_array($role, ['admin', 'operator'], true);

        $pengumuman = Pengumuman::untukRole($role)->with('pembuat')
            ->when(! $kelola, fn ($q) => $q->aktif())
            ->urut()->paginate(10);

        return view('pengumuman.index', compact('pengumuman', 'kelola'));
    }

    public function create()
    {
        return view('pengumuman.form', ['pengumuman' => new Pengumuman(['target' => 'semua', 'terbit_pada' => today()])]);
    }

    public function store(Request $request)
    {
        $pengumuman = Pengumuman::create($this->validasi($request) + ['dibuat_oleh' => $request->user()->id]);

        // Pengumuman terjadwal tidak memicu notifikasi; pembacanya tetap bisa melihatnya begitu terbit.
        if ($pengumuman->terbit_pada->lte(today())) {
            Notification::send(
                User::whereIn('role', $pengumuman->roleTujuan())->where('is_active', true)->whereKeyNot($request->user()->id)->get(),
                new Pengingat('Pengumuman: '.$pengumuman->judul, Str::limit($pengumuman->isi, 120), route('pengumuman.index'), "pengumuman:{$pengumuman->id}"),
            );
        }

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman diterbitkan.');
    }

    public function edit(Pengumuman $pengumuman)
    {
        return view('pengumuman.form', compact('pengumuman'));
    }

    public function update(Request $request, Pengumuman $pengumuman)
    {
        $pengumuman->update($this->validasi($request));

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman diperbarui.');
    }

    public function destroy(Pengumuman $pengumuman)
    {
        $pengumuman->delete();

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman dihapus.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'judul' => 'required|string|max:150',
            'isi' => 'required|string|max:5000',
            'target' => 'required|in:'.implode(',', array_keys(Pengumuman::TARGET)),
            'terbit_pada' => 'required|date',
            'berakhir_pada' => 'nullable|date|after_or_equal:terbit_pada',
        ]);
        $data['disematkan'] = $request->boolean('disematkan');

        return $data;
    }
}
