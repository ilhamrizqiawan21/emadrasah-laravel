<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'e-Madrasah') — Sistem Informasi Madrasah</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    @stack('styles')
</head>
<body class="em-body">
    @php
        $routeName = request()->route()?->getName();
        $routePrefix = \Illuminate\Support\Str::before((string) $routeName, '.');
        $sections = [
            'guru' => ['Master Data', 'Guru'],
            'kelas' => ['Master Data', 'Kelas'],
            'mapel' => ['Master Data', 'Mata Pelajaran'],
            'jam-pelajaran' => ['Master Data', 'Jam Pelajaran'],
            'tahun-pelajaran' => ['Master Data', 'Tahun Pelajaran'],
            'siswa' => ['Akademik', 'Siswa'],
            'buku-induk' => ['Akademik', 'Buku Induk'],
            'raport' => ['Akademik', 'Raport'],
            'jadwal' => ['Akademik', 'Jadwal'],
            'absensi' => ['Akademik', 'Absensi Guru'],
            'arsip-akademik' => ['Akademik', 'Arsip Akademik'],
            'surat-masuk' => ['Administrasi', 'Surat Masuk'],
            'surat-keluar' => ['Administrasi', 'Surat Keluar'],
            'template-surat' => ['Administrasi', 'Template Surat'],
            'tasks' => ['Administrasi', 'Tugas TU'],
            'sarana' => ['Sarpras', 'Sarana'],
            'kategori-sarana' => ['Sarpras', 'Kategori Sarana'],
            'users' => ['Sistem', 'Pengguna'],
        ];
        $breadcrumb = $sections[$routePrefix] ?? null;
    @endphp

    <div class="em-layout">
        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Main content -->
        <main class="em-main" id="emMain">
            <div class="container-fluid px-0">
                @if ($breadcrumb)
                    <nav class="em-breadcrumb" aria-label="Breadcrumb">
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                        <span>{{ $breadcrumb[0] }}</span>
                        <span aria-current="page">{{ $breadcrumb[1] }}</span>
                    </nav>
                @endif
                @include('components.alert')
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Mobile sidebar overlay -->
    <div class="em-sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Footer -->
    <footer class="em-footer">
        <p>&copy; {{ date('Y') }} e-Madrasah — MTs Al-Ihsan Batujajar</p>
    </footer>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/app.js') }}"></script>
    @stack('scripts')
</body>
</html>
