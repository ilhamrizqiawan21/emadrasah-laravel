<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | {{ $madrasah->nama_pendek }}</title>
    <link rel="icon" href="{{ $madrasah->faviconUrl() }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @include('partials.theme')
</head>
<body class="login-page">
    <main class="login-shell">
        <div class="login-card">
            <header class="login-head">
                <div class="login-logo">
                    @if ($madrasah->get('logo'))
                        <img src="{{ $madrasah->logoUrl() }}" alt="Logo {{ $madrasah->nama_pendek }}" class="login-logo__img">
                    @else
                        <i class="fas fa-mosque"></i>
                    @endif
                </div>
                <p class="login-brand__name">{{ $madrasah->nama }}</p>
            </header>

            @yield('content')
        </div>
        <p class="login-footer">&copy; {{ date('Y') }} {{ $madrasah->nama }}</p>
    </main>
</body>
</html>
