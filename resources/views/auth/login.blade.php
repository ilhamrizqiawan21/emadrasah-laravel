<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk | {{ $madrasah->nama_pendek }}</title>
    <link rel="icon" href="{{ $madrasah->faviconUrl() }}">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
    @include('partials.theme')
</head>
<body class="login-page">

    <main class="login-split">
        {{-- Panel merek: identitas madrasah dari menu Pengaturan --}}
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

        {{-- Panel form --}}
        <section class="login-panel">
            <div class="login-panel__inner">
                <h2 class="login-heading">Masuk</h2>
                <p class="login-lead">Gunakan akun yang diberikan oleh administrator.</p>

                @if(session('status'))
                    <div class="alert em-alert em-alert-success mb-4" role="alert">
                        <div class="em-alert-icon"><i class="fas fa-circle-check"></i></div>
                        <div class="em-alert-content">{{ session('status') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert em-alert em-alert-danger mb-4" role="alert">
                        <div class="em-alert-icon"><i class="fas fa-circle-exclamation"></i></div>
                        <div class="em-alert-content">{{ $errors->first() }}</div>
                    </div>
                @endif

                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="form-floating mb-3">
                        <input type="email" name="email" class="form-control" id="email" placeholder="nama@email.com" autocomplete="username" required autofocus value="{{ old('email') }}">
                        <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                    </div>

                    <div class="form-floating mb-3 login-password">
                        <input type="password" name="password" class="form-control" id="password" placeholder="Password" autocomplete="current-password" required>
                        <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                        <button type="button" class="login-password__toggle" id="togglePassword" aria-label="Tampilkan password" aria-pressed="false">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <div class="form-check mb-4 px-1 ms-1">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">Ingat saya di perangkat ini</label>
                    </div>

                    <p class="text-end mb-3 mt-n2"><a href="{{ route('password.request') }}" class="small">Lupa password?</a></p>

                    <button type="submit" class="btn btn-primary btn-login w-100">
                        Masuk ke Sistem <i class="fas fa-arrow-right ms-2"></i>
                    </button>
                </form>

                <p class="login-footer">&copy; {{ date('Y') }} {{ $madrasah->nama }}</p>
            </div>
        </section>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('password');
            const toggle = document.getElementById('togglePassword');
            toggle.addEventListener('click', function () {
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                toggle.setAttribute('aria-pressed', String(show));
                toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
                toggle.firstElementChild.className = show ? 'fas fa-eye-slash' : 'fas fa-eye';
            });
        });
    </script>
</body>
</html>
