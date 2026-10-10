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
    <main class="login-split">
        <section class="login-brand">
            <div class="login-brand__inner">
                <div class="login-logo">
                    @if ($madrasah->get('logo'))
                        <img src="{{ $madrasah->logoUrl() }}" alt="Logo {{ $madrasah->nama_pendek }}" class="login-logo__img">
                    @else
                        <i class="fas fa-mosque"></i>
                    @endif
                </div>
                <h1 class="login-brand__name">{{ $madrasah->nama }}</h1>
                <p class="login-brand__tagline">Sistem Informasi Administrasi TU Terpadu</p>
            </div>
            <p class="login-brand__foot">e-Madrasah</p>
        </section>

        <section class="login-panel">
            <div class="login-panel__inner">
                @yield('content')
                <p class="login-footer">&copy; {{ date('Y') }} {{ $madrasah->nama }}</p>
            </div>
        </section>
    </main>
</body>
</html>
