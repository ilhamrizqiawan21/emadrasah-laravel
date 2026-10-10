<?php

namespace App\Http\Controllers;

use App\Support\Madrasah;
use App\Support\ThemePalette;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Pengaturan: identity and look of this installation (admin only).
 */
class PengaturanController extends Controller
{
    public function edit(Madrasah $madrasah)
    {
        return view('pengaturan.edit', ['presets' => Madrasah::PRESETS]);
    }

    public function update(Request $request, Madrasah $madrasah)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:150',
            'nama_pendek' => 'required|string|max:60',
            'nama_lengkap' => 'required|string|max:200',
            'npsn' => ['nullable', 'regex:/^[0-9]{8}$/'],
            'alamat' => 'nullable|string|max:500',
            'telepon' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s()]+$/'],
            'email' => 'nullable|email|max:150',
            'website' => 'nullable|url:http,https|max:200',
            'kepala_nama' => 'nullable|string|max:150',
            'kepala_nip' => 'nullable|string|max:30',
            'warna_utama' => [
                'required',
                'regex:/^#[0-9a-fA-F]{6}$/',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (ThemePalette::isHex($value) && ThemePalette::contrastWithWhite($value) < ThemePalette::MIN_CONTRAST) {
                        $fail('Warna terlalu terang: teks putih pada tombol tidak akan terbaca. Pilih warna yang lebih gelap.');
                    }
                },
            ],
            'logo' => 'nullable|file|image|mimes:png,jpg,jpeg,webp|max:1024|dimensions:min_width=64,min_height=64,max_width=2000,max_height=2000',
            'favicon' => 'nullable|file|mimes:png,ico|max:256',
            'hapus_logo' => 'sometimes|boolean',
            'hapus_favicon' => 'sometimes|boolean',
        ], [
            'npsn.regex' => 'NPSN harus 8 digit angka.',
            'telepon.regex' => 'Telepon hanya boleh berisi angka, spasi, +, -, dan tanda kurung.',
            'warna_utama.regex' => 'Warna harus berformat #rrggbb.',
            'logo.image' => 'Logo harus berupa gambar PNG, JPG, atau WebP.',
            'logo.mimes' => 'Logo harus berformat PNG, JPG, atau WebP.',
            'logo.max' => 'Ukuran logo maksimal 1 MB.',
            'logo.dimensions' => 'Dimensi logo minimal 64x64 dan maksimal 2000x2000 piksel.',
            'favicon.mimes' => 'Favicon harus berformat PNG atau ICO.',
            'favicon.max' => 'Ukuran favicon maksimal 256 KB.',
        ]);

        $values = [];
        foreach (Madrasah::TEXT_KEYS as $key) {
            $values[$key] = $this->nullIfBlank($validated[$key] ?? null);
        }
        $values['warna_utama'] = strtolower($validated['warna_utama']);

        foreach (['logo', 'favicon'] as $type) {
            $old = $madrasah->get($type);

            if ($request->hasFile($type)) {
                $values[$type] = $request->file($type)->store('branding', 'local');
                $this->deleteBranding($old);
            } elseif ($request->boolean("hapus_{$type}")) {
                $values[$type] = null;
                $this->deleteBranding($old);
            }
        }

        $madrasah->save($values);

        return redirect()->route('pengaturan.edit')->with('success', 'Pengaturan berhasil disimpan.');
    }

    private function nullIfBlank(?string $value): ?string
    {
        $value = $value === null ? null : trim($value);

        return $value === '' ? null : $value;
    }

    /** Removes an uploaded file, but only ever from the branding folder. */
    private function deleteBranding(?string $path): void
    {
        if ($path && str_starts_with($path, 'branding/') && ! str_contains($path, '..')) {
            Storage::disk('local')->delete($path);
        }
    }
}
