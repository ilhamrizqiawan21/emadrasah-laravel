<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

/**
 * Menyajikan berkas upload (surat, dokumen siswa, lampiran) yang disimpan
 * di disk privat. Hanya bisa diakses setelah login dan sesuai role route-nya.
 */
class FileController extends Controller
{
    /** Folder upload yang boleh disajikan. */
    private const ALLOWED_DIRS = [
        'surat-masuk',
        'surat-keluar',
        'sarana',
        'arsip-akademik',
        'task-attachments',
        'siswa-dokumen',
        'siswa-foto',
    ];

    /** Tipe yang aman ditampilkan langsung di browser. */
    private const INLINE_MIMES = ['application/pdf', 'image/jpeg', 'image/png'];

    public function show(string $path)
    {
        $path = ltrim($path, '/');

        // Tolak path traversal / null byte, dan folder di luar daftar.
        abort_if(str_contains($path, '..') || str_contains($path, "\0") || str_contains($path, '\\'), 404);
        abort_unless(in_array(explode('/', $path)[0], self::ALLOWED_DIRS, true), 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($path), 404);

        $inline = in_array($disk->mimeType($path), self::INLINE_MIMES, true);

        return $disk->response($path, basename($path), [
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; img-src 'self' data:; style-src 'unsafe-inline'; sandbox",
            'Cache-Control' => 'private, max-age=0, must-revalidate',
        ], $inline ? 'inline' : 'attachment');
    }
}
