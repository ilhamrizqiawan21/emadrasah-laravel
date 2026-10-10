{{--
    Bar atas semua ukuran layar. Breadcrumb mengambil judul dari @section('title') halaman.
    Tombol menu hanya tampil di ponsel (< 992px); di desktop sidebar yang mengatur menu.
--}}
@php
    $topbarTitle = trim($__env->yieldContent('title'));
    $topbarUser  = auth()->user();
    $topbarRole  = ucfirst(str_replace('_', ' ', $topbarUser?->role ?? 'Admin'));
@endphp
<header class="em-topbar">
    <button type="button" class="em-mobile-menu-btn d-lg-none" id="sidebarToggleMobile" aria-label="Buka menu">
        <i class="fas fa-bars"></i>
    </button>

    <nav class="em-breadcrumb" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="em-breadcrumb__home" aria-label="Beranda">
            <i class="fas fa-house"></i>
        </a>
        @if($topbarTitle !== '' && $topbarTitle !== 'Dashboard')
            <i class="fas fa-chevron-right em-breadcrumb__sep" aria-hidden="true"></i>
            <span class="em-breadcrumb__current" aria-current="page">{{ $topbarTitle }}</span>
        @else
            <span class="em-breadcrumb__current" aria-current="page">Dashboard</span>
        @endif
    </nav>

    <div class="em-topbar__spacer"></div>

    <div class="dropdown em-profile">
        <button type="button" class="em-profile__btn" data-bs-toggle="dropdown" data-bs-offset="0,8" aria-expanded="false" aria-label="Menu akun">
            <span class="em-user-avatar"><span>{{ strtoupper(substr($topbarUser?->name ?? 'A', 0, 1)) }}</span></span>
            <span class="em-profile__meta d-none d-md-flex">
                <span class="em-profile__name">{{ $topbarUser?->name ?? 'Admin' }}</span>
                <span class="em-profile__role">{{ $topbarRole }}</span>
            </span>
            <i class="fas fa-chevron-down em-profile__caret d-none d-md-inline"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-end em-profile__menu">
            <div class="em-profile__head">
                <strong>{{ $topbarUser?->name ?? 'Admin' }}</strong>
                <small>{{ $topbarUser?->email }}</small>
                <span class="badge em-profile__badge">{{ $topbarRole }}</span>
            </div>
            <div class="dropdown-divider"></div>
            <a href="{{ route('akun.sandi') }}" class="dropdown-item"><i class="fas fa-key me-2"></i> Ganti Kata Sandi</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="dropdown-item em-profile__logout">
                    <i class="fas fa-sign-out-alt me-2"></i> Keluar
                </button>
            </form>
        </div>
    </div>
</header>

