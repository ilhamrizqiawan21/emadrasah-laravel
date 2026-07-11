<?php

namespace App\Http\Controllers;

use App\Models\SaranaPrasarana;
use App\Models\KategoriSarana;
use App\Models\PeminjamanSarana;
use App\Models\PemeliharaanSarana;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;

class SaranaController extends Controller
{
    public function index()
    {
        $sarana = SaranaPrasarana::with('kategori')->orderBy('nama_sarana')->paginate(15);
        return view('sarana.index', compact('sarana'));
    }

    public function create()
    {
        $kategori = KategoriSarana::all();
        return view('sarana.create', compact('kategori'));
    }

    public function store(Request $request)
    {
        // Apply rate limiting for uploads
        if (RateLimiter::tooManyAttempts('upload:' . auth()->id(), 10)) {
            return redirect()->back()
                ->withErrors(['foto' => 'Terlalu banyak upload. Silakan tunggu beberapa menit.'])
                ->withInput();
        }

        $validated = $request->validate([
            'kode_sarana' => 'required|unique:sarana_prasarana',
            'nama_sarana' => 'required',
            'kategori_id' => 'required|exists:kategori_sarana,id',
            'spesifikasi' => 'nullable|string',
            'jumlah' => 'required|integer|min:1',
            'stok_tersedia' => 'nullable|integer|min:0',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
            'lokasi_ruang' => 'nullable|string|max:100',
            'tahun_pengadaan' => 'nullable|digits:4|between:1900,' . date('Y'),
            'foto' => 'nullable|image|mimes:jpeg,png,gif,webp|max:' . config('app.upload.max_size', 2048),
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            
            // Additional security: verify file is actually an image
            if (!$file->isValid()) {
                RateLimiter::hit('upload:' . auth()->id());
                return redirect()->back()
                    ->withErrors(['foto' => 'File upload tidak valid.'])
                    ->withInput();
            }
            
            // Verify MIME type
            $allowedMimeTypes = config('app.upload.allowed_image_types', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
            if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                RateLimiter::hit('upload:' . auth()->id());
                return redirect()->back()
                    ->withErrors(['foto' => 'Tipe file tidak diizinkan. Hanya JPEG, PNG, GIF, dan WebP yang diperbolehkan.'])
                    ->withInput();
            }
            
            // Generate safe filename
            $extension = $file->getClientOriginalExtension();
            $filename = 'sarana_' . time() . '_' . bin2hex(random_bytes(16)) . '.' . $extension;
            $path = $file->storeAs('sarana', $filename, 'public');
            $validated['foto'] = $path;
        }

        SaranaPrasarana::create($validated);
        return redirect()->route('sarana.index')->with('success', 'Sarana berhasil ditambahkan.');
    }

    public function edit(SaranaPrasarana $sarana)
    {
        $kategori = KategoriSarana::all();
        return view('sarana.edit', compact('sarana', 'kategori'));
    }

    public function update(Request $request, SaranaPrasarana $sarana)
    {
        // Apply rate limiting for uploads
        if (RateLimiter::tooManyAttempts('upload:' . auth()->id(), 10)) {
            return redirect()->back()
                ->withErrors(['foto' => 'Terlalu banyak upload. Silakan tunggu beberapa menit.'])
                ->withInput();
        }

        $validated = $request->validate([
            'kode_sarana' => 'required|unique:sarana_prasarana,kode_sarana,' . $sarana->id,
            'nama_sarana' => 'required',
            'kategori_id' => 'required|exists:kategori_sarana,id',
            'spesifikasi' => 'nullable|string',
            'jumlah' => 'required|integer|min:1',
            'stok_tersedia' => 'nullable|integer|min:0',
            'kondisi' => 'required|in:baik,rusak_ringan,rusak_berat,hilang',
            'lokasi_ruang' => 'nullable|string|max:100',
            'tahun_pengadaan' => 'nullable|digits:4|between:1900,' . date('Y'),
            'foto' => 'nullable|image|mimes:jpeg,png,gif,webp|max:' . config('app.upload.max_size', 2048),
        ]);

        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            
            // Additional security: verify file is actually an image
            if (!$file->isValid()) {
                RateLimiter::hit('upload:' . auth()->id());
                return redirect()->back()
                    ->withErrors(['foto' => 'File upload tidak valid.'])
                    ->withInput();
            }
            
            // Verify MIME type
            $allowedMimeTypes = config('app.upload.allowed_image_types', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
            if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
                RateLimiter::hit('upload:' . auth()->id());
                return redirect()->back()
                    ->withErrors(['foto' => 'Tipe file tidak diizinkan. Hanya JPEG, PNG, GIF, dan WebP yang diperbolehkan.'])
                    ->withInput();
            }
            
            // Delete old file if exists
            if ($sarana->foto && \Storage::disk('public')->exists($sarana->foto)) {
                \Storage::disk('public')->delete($sarana->foto);
            }
            
            // Generate safe filename
            $extension = $file->getClientOriginalExtension();
            $filename = 'sarana_' . time() . '_' . bin2hex(random_bytes(16)) . '.' . $extension;
            $path = $file->storeAs('sarana', $filename, 'public');
            $validated['foto'] = $path;
        }

        $sarana->update($validated);
        return redirect()->route('sarana.index')->with('success', 'Sarana berhasil diupdate.');
    }

    public function destroy(SaranaPrasarana $sarana)
    {
        // Delete associated file if exists
        if ($sarana->foto && \Storage::disk('public')->exists($sarana->foto)) {
            \Storage::disk('public')->delete($sarana->foto);
        }
        
        $sarana->delete();
        return redirect()->route('sarana.index')->with('success', 'Sarana berhasil dihapus.');
    }

    public function peminjaman(SaranaPrasarana $sarana)
    {
        $peminjaman = PeminjamanSarana::where('sarana_id', $sarana->id)->orderBy('tanggal_pinjam', 'desc')->paginate(10);
        return view('sarana.peminjaman', compact('sarana', 'peminjaman'));
    }

    public function storePeminjaman(Request $request, SaranaPrasarana $sarana)
    {
        $validated = $request->validate([
            'peminjam' => 'required',
            'tipe_peminjam' => 'required|in:guru,siswa',
            'tanggal_pinjam' => 'required|date',
        ]);
        $validated['sarana_id'] = $sarana->id;
        $validated['status'] = 'dipinjam';
        PeminjamanSarana::create($validated);
        return redirect()->route('sarana.peminjaman', $sarana)->with('success', 'Peminjaman dicatat.');
    }

    public function kembalikan(PeminjamanSarana $peminjaman)
    {
        $peminjaman->update([
            'tanggal_kembali' => today(),
            'status' => 'dikembalikan',
        ]);
        return back()->with('success', 'Sarana dikembalikan.');
    }

    public function pemeliharaan(SaranaPrasarana $sarana)
    {
        $pemeliharaan = PemeliharaanSarana::where('sarana_id', $sarana->id)->orderBy('tanggal_pemeliharaan', 'desc')->paginate(10);
        return view('sarana.pemeliharaan', compact('sarana', 'pemeliharaan'));
    }

    public function storePemeliharaan(Request $request, SaranaPrasarana $sarana)
    {
        $validated = $request->validate([
            'tanggal_pemeliharaan' => 'required|date',
            'biaya' => 'nullable|numeric|min:0',
            'keterangan' => 'nullable',
            'status' => 'nullable|in:proses,selesai',
            'tanggal_selesai' => 'nullable|date',
            'teknisi' => 'nullable',
        ]);
        $validated['sarana_id'] = $sarana->id;
        PemeliharaanSarana::create($validated);
        return redirect()->route('sarana.pemeliharaan', $sarana)->with('success', 'Pemeliharaan dicatat.');
    }

}
