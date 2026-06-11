<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunPelajaran;
use App\Models\OrangTuaWali;
use App\Models\PerkembanganSiswa;
use App\Models\SiswaDokumen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class BukuIndukController extends Controller
{
    public function index()
    {
        $siswa = Siswa::with(['kelas', 'tahunPelajaran'])->latest()->paginate(20);
        return view('buku-induk.index', compact('siswa'));
    }

    public function create()
    {
        $kelas = Kelas::all();
        $tahunPelajaran = TahunPelajaran::all();
        return view('buku-induk.create', compact('kelas', 'tahunPelajaran'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'nis' => 'required|unique:siswa,nis',
            'nisn' => 'nullable|unique:siswa,nisn',
            'nik' => 'nullable|unique:siswa,nik|digits:16',
        ]);

        DB::beginTransaction();
        try {
            // 1. Simpan Data Utama Siswa
            $siswa = Siswa::create($request->only((new Siswa)->getFillable()));

            // 2. Simpan Data Orang Tua
            $ortuData = $request->only((new OrangTuaWali)->getFillable());
            $ortuData['siswa_id'] = $siswa->id;
            OrangTuaWali::create($ortuData);

            // 3. Simpan Data Perkembangan
            $perkembanganData = $request->only((new PerkembanganSiswa)->getFillable());
            $perkembanganData['siswa_id'] = $siswa->id;
            // Map legacy form field names to current DB column names
            if ($request->filled('asal_sekolah') && empty($perkembanganData['asal_madrasah'])) {
                $perkembanganData['asal_madrasah'] = $request->input('asal_sekolah');
            }
            PerkembanganSiswa::create($perkembanganData);

            // 4. Simpan Dokumen (Brankas Digital)
            if ($request->hasFile('dokumen')) {
                foreach ($request->file('dokumen') as $jenis => $file) {
                    if ($file) {
                        $filename = time() . '_' . str_replace(' ', '_', $jenis) . '_' . $siswa->nis . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('siswa-dokumen', $filename, 'public');
                        
                        SiswaDokumen::create([
                            'siswa_id' => $siswa->id,
                            'jenis_dokumen' => $jenis,
                            'file_path' => $path,
                            'nama_file' => $file->getClientOriginalName()
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('buku-induk.index')->with('success', 'Data Buku Induk Siswa berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(Siswa $siswa)
    {
        $siswa->load(['orangTuaWali', 'perkembangan', 'dokumen']);
        $kelas = Kelas::all();
        $tahunPelajaran = TahunPelajaran::all();
        return view('buku-induk.edit', compact('siswa', 'kelas', 'tahunPelajaran'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'nis' => 'required|unique:siswa,nis,' . $siswa->id,
            'nisn' => 'nullable|unique:siswa,nisn,' . $siswa->id,
            'nik' => 'nullable|unique:siswa,nik,' . $siswa->id . '|digits:16',
        ]);

        DB::beginTransaction();
        try {
            // 1. Update Utama
            $siswa->update($request->only((new Siswa)->getFillable()));

            // 2. Ortu
            $siswa->orangTuaWali()->updateOrCreate(['siswa_id' => $siswa->id], $request->only((new OrangTuaWali)->getFillable()));

            // 3. Perkembangan
            $perkembanganData = $request->only((new PerkembanganSiswa)->getFillable());
            if ($request->filled('asal_sekolah') && empty($perkembanganData['asal_madrasah'])) {
                $perkembanganData['asal_madrasah'] = $request->input('asal_sekolah');
            }
            $siswa->perkembangan()->updateOrCreate(['siswa_id' => $siswa->id], $perkembanganData);

            // 4. Dokumen Baru
            if ($request->hasFile('dokumen')) {
                foreach ($request->file('dokumen') as $jenis => $file) {
                    if ($file) {
                        $filename = time() . '_' . str_replace(' ', '_', $jenis) . '_' . $siswa->nis . '.' . $file->getClientOriginalExtension();
                        $path = $file->storeAs('siswa-dokumen', $filename, 'public');
                        $siswa->dokumen()->updateOrCreate(['jenis_dokumen' => $jenis], ['file_path' => $path, 'nama_file' => $file->getClientOriginalName()]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('buku-induk.index')->with('success', 'Data Buku Induk Siswa berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(Siswa $siswa)
    {
        $siswa->load(['kelas', 'tahunPelajaran', 'orangTuaWali', 'perkembangan', 'dokumen']);
        return view('buku-induk.show', compact('siswa'));
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return redirect()->route('buku-induk.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function exportPdf(Siswa $siswa)
    {
        $siswa->load(['kelas', 'tahunPelajaran', 'orangTuaWali', 'perkembangan']);
        $pdf = Pdf::loadView('buku-induk.pdf', compact('siswa'))->setPaper('a4', 'portrait');
        return $pdf->stream('Buku_Induk_'.$siswa->nis.'.pdf');
    }
}
