<?php

/*
|--------------------------------------------------------------------------
| Trusted proxies
|--------------------------------------------------------------------------
|
| Dibaca oleh middleware TrustProxies bawaan Laravel saat request, ketika
| konfigurasi sudah dimuat (juga setelah `php artisan config:cache`).
|
| Isi TRUSTED_PROXIES di .env bila aplikasi berada di belakang Nginx,
| Cloudflare, atau load balancer: daftar IP dipisah koma, atau * bila IP
| proxy tidak bisa dipastikan. Kosong = header X-Forwarded-* diabaikan.
|
*/

return [
    'proxies' => env('TRUSTED_PROXIES') ?: null,
];
