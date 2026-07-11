<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'e-Madrasah') — Sistem Informasi Madrasah</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Vite Assets - Bootstrap, Icons & Custom CSS bundled -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="em-body">

    <div class="em-layout">
        <!-- Sidebar -->
        @include('components.sidebar')

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

    <!-- Scripts already loaded via @vite in head -->
    @stack('scripts')
</body>
</html>