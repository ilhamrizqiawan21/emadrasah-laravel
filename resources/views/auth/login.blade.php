<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | e-Madrasah v2.0</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Vite Assets - Bootstrap, Icons & Custom CSS bundled -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --login-bg: #f8fafc;
        }
        body.login-page {
            background-color: var(--login-bg);
            background-image: 
                radial-gradient(at 0% 0%, rgba(26, 122, 82, 0.05) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(212, 160, 23, 0.05) 0px, transparent 50%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border: none;
            border-radius: var(--em-r-xl);
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
            overflow: hidden;
            animation: em-fade-in 0.6s var(--em-ease) both;
        }
        .login-header {
            background: linear-gradient(135deg, var(--em-green-950) 0%, var(--em-green-800) 100%);
            padding: 40px 30px;
            text-align: center;
            position: relative;
        }
        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 40px;
            background: var(--em-white);
            clip-path: ellipse(60% 40px at 50% 40px);
        }
        .login-logo {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: var(--em-r-lg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            color: #fff;
            font-size: 1.8rem;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        }
        .login-title {
            color: #fff;
            font-weight: 800;
            font-size: 1.5rem;
            margin-bottom: 5px;
            letter-spacing: -0.5px;
        }
        .login-subtitle {
            color: rgba(255,255,255,0.6);
            font-size: 0.85rem;
        }
        .login-body {
            padding: 20px 40px 40px;
            background: var(--em-white);
        }
        .form-floating > .form-control:focus ~ label,
        .form-floating > .form-control:not(:placeholder-shown) ~ label {
            color: var(--em-primary);
            opacity: 0.8;
        }
        .btn-login {
            padding: 12px;
            font-weight: 700;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }
        .login-footer {
            text-align: center;
            margin-top: 25px;
            font-size: 0.75rem;
            color: var(--em-gray-400);
        }
        @keyframes em-fade-in {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="login-page">

    <div class="login-card card">
        <div class="login-header">
            <div class="login-logo">
                <i class="fas fa-mosque"></i>
            </div>
            <h1 class="login-title">e-Madrasah</h1>
            <p class="login-subtitle">Sistem Informasi Administrasi TU Terpadu</p>
        </div>
        
        <div class="login-body">
            @if($errors->any())
                <div class="alert em-alert em-alert-danger mb-4">
                    <div class="em-alert-icon"><i class="fas fa-circle-exclamation"></i></div>
                    <div class="em-alert-content">{{ $errors->first() }}</div>
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="form-floating mb-3">
                    <input type="email" name="email" class="form-control" id="email" placeholder="name@example.com" required value="{{ old('email') }}">
                    <label for="email"><i class="fas fa-envelope me-2"></i>Username</label>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" name="password" class="form-control" id="password" placeholder="Password" required>
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 px-1">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember">
                        <label class="form-check-label small text-muted" for="remember">
                            Ingat Saya
                        </label>
                    </div>
                    <a href="#" class="small text-primary fw-bold">Lupa Password?</a>
                </div>

                <button type="submit" class="btn btn-primary btn-login w-100 shadow-sm">
                    Masuk ke Sistem <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </form>

            <div class="login-footer">
                <p>&copy; 2026 MTs Al-Ihsan Batujajar<br>Versi 2.0 Modern Edition</p>
            </div>
        </div>
    </div>

    <!-- Scripts already loaded via @vite in head -->
</body>
</html>
