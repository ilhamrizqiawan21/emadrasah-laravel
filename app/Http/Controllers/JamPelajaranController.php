<?php

namespace App\Http\Controllers;

use App\Models\JamPelajaran;
use Illuminate\Http\Request;

class JamPelajaranController extends Controller
{
    public function index()
    {
        $jamPelajaran = JamPelajaran::orderBy('hari')->orderBy('sesi_ke')->paginate(20);

        return view('jam-pelajaran.index', compact('jamPelajaran'));
    }

    public function create()
    {
        return view('jam-pelajaran.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'sesi_ke' => 'required|integer|min:1',
            'jam_mulai' => 'required|date_format:H:i,H:i:s',
            'jam_selesai' => 'required|date_format:H:i,H:i:s|after:jam_mulai',
        ]);
        JamPelajaran::create($validated);

        return redirect()->route('jam-pelajaran.index')->with('success', 'Jam pelajaran berhasil ditambahkan.');
    }

    public function edit(JamPelajaran $jamPelajaran)
    {
        return view('jam-pelajaran.edit', compact('jamPelajaran'));
    }

    public function update(Request $request, JamPelajaran $jamPelajaran)
    {
        $validated = $request->validate([
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'sesi_ke' => 'required|integer|min:1',
            'jam_mulai' => 'required|date_format:H:i,H:i:s',
            'jam_selesai' => 'required|date_format:H:i,H:i:s|after:jam_mulai',
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
