<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'e-Madrasah') — Sistem Informasi Madrasah</title>
    <link rel="icon" href="{{ $madrasah->faviconUrl() }}">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @include('partials.theme')
    @stack('styles')
</head>
<body class="em-body em-preload">

    <div class="em-layout">
        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Topbar -->
        @include('components.topbar')
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
        <p>&copy; {{ date('Y') }} e-Madrasah — {{ $madrasah->nama }}</p>
    </footer>

    @stack('scripts')
</body>
</html>