# ADR 0001: Frontend Stack Inertia React TypeScript

Tanggal: 2026-08-28

## Status

Accepted.

## Keputusan

Project eMadrasah memakai Laravel monolith dengan Inertia, React, TypeScript, Vite, Tailwind CSS, dan komponen UI bergaya shadcn/ui untuk migrasi bertahap dari Blade.

## Konteks

Project memiliki banyak controller Laravel, session auth, validasi server, upload file, dan export PDF. Memisahkan frontend menjadi API + SPA penuh akan menambah kompleksitas auth, routing, upload, dan deployment.

## Konsekuensi

- Laravel tetap menjadi pemilik routing, middleware, session, validasi, dan data access.
- React menggantikan halaman admin secara bertahap melalui `Inertia::render`.
- Blade tetap dipertahankan untuk PDF dan fallback halaman yang belum dimigrasi.
- Komponen UI baru dibangun di `resources/js/Components` dengan pola shadcn/ui.
