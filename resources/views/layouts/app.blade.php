<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'e-Madrasah') — Sistem Informasi Madrasah</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="{{ asset('css/custom.css') . '?v=' . filemtime(public_path('css/custom.css')) }}">
    @stack('styles')
</head>
<body class="em-body em-preload">

    <div class="em-layout">
        <!-- Sidebar -->
        @include('components.sidebar')
        <script>
            /* Terapkan keadaan ciut sebelum render pertama agar tidak berkedip/beranimasi setiap pindah halaman */
            (function () {
                try {
                    if (window.innerWidth >= 992 && localStorage.getItem('em_sidebar_collapsed') === 'true') {
                        document.getElementById('emSidebar').classList.add('is-collapsed');
                    }
                } catch (e) {}
            })();
        </script>

        <!-- Main content -->
        <main class="em-main" id="emMain">
            <div class="container-fluid px-0">
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
    <script src="{{ asset('js/app.js') . '?v=' . filemtime(public_path('js/app.js')) }}"></script>
    @stack('scripts')
</body>
</html>