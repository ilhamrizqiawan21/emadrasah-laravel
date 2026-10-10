<?php

/*
|--------------------------------------------------------------------------
| Madrasah identity: initial values
|--------------------------------------------------------------------------
|
| These are only the starting values for a fresh installation. Once the
| admin saves the Pengaturan page, the values stored in the database take
| over, so clients never need to touch this file or .env.
|
| Logo and favicon are uploaded from the Pengaturan page (null = default).
|
*/

return [

    // Short name shown in the sidebar, e.g. "MTs Al-Ihsan".
    'nama_pendek' => env('MADRASAH_SHORT_NAME', 'Nama Madrasah'),

    // Display name used in banners, footers and headings.
    'nama' => env('MADRASAH_NAME', 'Nama Madrasah'),

    // Formal name used in printed documents (PDF).
    'nama_lengkap' => env('MADRASAH_FULL_NAME', 'Nama Lengkap Madrasah'),

    'npsn' => env('MADRASAH_NPSN'),
    'alamat' => env('MADRASAH_ALAMAT'),
    'telepon' => env('MADRASAH_TELEPON'),
    'email' => env('MADRASAH_EMAIL'),
    'website' => env('MADRASAH_WEBSITE'),

    // Head of the madrasah, printed under signatures.
    'kepala_nama' => env('MADRASAH_KEPALA_NAMA'),
    'kepala_nip' => env('MADRASAH_KEPALA_NIP'),

    // Brand colour (#rrggbb). The default reproduces the built-in emerald theme.
    'warna_utama' => env('MADRASAH_COLOR', '#047857'),

    'logo' => null,
    'favicon' => null,

];
