<?php

namespace App\Http\Controllers;

use App\Support\Madrasah;
use Illuminate\Support\Facades\Storage;

/**
 * Serves the madrasah logo and favicon. Public on purpose: the login page and
 * browser tab need them before anyone is signed in. Only files saved by the
 * Pengaturan page (under branding/) are ever served.
 */
class BrandingController extends Controller
{
    public function show(Madrasah $madrasah, string $type)
    {
        abort_unless(in_array($type, ['logo', 'favicon'], true), 404);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
            // The URL carries a ?v= hash that changes whenever the file does.
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ];

        $path = $madrasah->brandingPath($type);
        $disk = Storage::disk('local');

        if ($path && $disk->exists($path)) {
            return $disk->response($path, basename($path), $headers, 'inline');
        }

        return response()->file(
            public_path($type === 'logo' ? 'images/logo.png' : 'favicon.ico'),
            $headers
        );
    }
}
