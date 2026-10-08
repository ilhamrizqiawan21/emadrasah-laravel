<aside class="em-sidebar" id="emSidebar">

    {{-- ── Header: Brand + Toggle ── --}}
    <div class="em-sidebar__header">
        <div class="em-brand">
            <div class="em-brand__logo">
                <img src="{{ $madrasah->logoUrl() }}" alt="Logo" width="24" height="24">
            </div>
            <div class="em-brand__text">
                <span class="em-brand__title">e-Madrasah</span>
                <span class="em-brand__sub">{{ $madrasah->nama_pendek }}</span>
            </div>
        </div>
        <button type="button" class="em-sidebar-toggle" id="sidebarToggleDesktop" aria-label="Ciutkan atau lebarkan menu" title="Ciutkan / lebarkan menu">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button type="button" class="em-sidebar__close d-lg-none" id="sidebarClose" aria-label="Tutup menu">
            <i class="fas fa-xmark"></i>
        </button>
    </div>

    {{-- ── Navigation ── --}}
    @php($canManage = in_array(auth()->user()?->role, ['admin', 'operator'], true))
    <div class="em-sidebar__inner">
        <nav class="em-nav">

            <div class="em-nav__group">
                <span class="em-nav__label">Menu Utama</span>
                <a href="{{ route('dashboard') }}"
                   class="em-nav__link {{ request()->routeIs('dashboard') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Dashboard">
                    <span class="em-nav__icon"><i class="fas fa-gauge-high"></i></span>
                    <span class="em-nav__text">Dashboard</span>
                </a>
            </div>

            @if($canManage)
            <div class="em-nav__group">
                <span class="em-nav__label">Master Data</span>
                <a href="{{ route('guru.index') }}"
                   class="em-nav__link {{ request()->routeIs('guru.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Data Guru">
                    <span class="em-nav__icon"><i class="fas fa-chalkboard-user"></i></span>
                    <span class="em-nav__text">Data Guru</span>
                </a>
                <a href="{{ route('kelas.index') }}"
                   class="em-nav__link {{ request()->routeIs('kelas.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Data Kelas">
                    <span class="em-nav__icon"><i class="fas fa-door-open"></i></span>
                    <span class="em-nav__text">Data Kelas</span>
                </a>
                <a href="{{ route('mapel.index') }}"
                   class="em-nav__link {{ request()->routeIs('mapel.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Mata Pelajaran">
                    <span class="em-nav__icon"><i class="fas fa-book-open"></i></span>
                    <span class="em-nav__text">Mata Pelajaran</span>
                </a>
                <a href="{{ route('jam-pelajaran.index') }}"
                   class="em-nav__link {{ request()->routeIs('jam-pelajaran.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Jam Pelajaran">
                    <span class="em-nav__icon"><i class="fas fa-clock"></i></span>
                    <span class="em-nav__text">Jam Pelajaran</span>
                </a>
                <a href="{{ route('tahun-pelajaran.index') }}"
                   class="em-nav__link {{ request()->routeIs('tahun-pelajaran.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Tahun Pelajaran">
                    <span class="em-nav__icon"><i class="fas fa-calendar"></i></span>
                    <span class="em-nav__text">Tahun Pelajaran</span>
                </a>
            </div>
            @endif

            <div class="em-nav__group">
                <span class="em-nav__label">Akademik</span>
                @if($canManage)
                {{-- ── MANAJEMEN SISWA (dropdown) ── --}}
                <div class="em-nav__dropdown {{ request()->routeIs('siswa.*', 'buku-induk.*', 'raport.*') ? 'is-open' : '' }}" id="dropdown-kesiswaan-parent">
                    <div class="em-nav__dropdown-toggle" data-dropdown="kesiswaan" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
                        <span class="em-nav__icon"><i class="fas fa-user-graduate"></i></span>
                        <span class="em-nav__text">Kesiswaan</span>
                        <i class="fas fa-chevron-down em-dropdown-icon"></i>
                    </div>
                    <div class="em-nav__dropdown-menu">
                        <div class="em-nav__flyout-title">Kesiswaan</div>
                        <a href="{{ route('siswa.index') }}"
                           class="em-nav__link em-nav__link--sub {{ request()->routeIs('siswa.*') ? 'is-active' : '' }}">
                            <span class="em-nav__icon"><i class="fas fa-users"></i></span>
                            <span class="em-nav__text">Data Ringkas</span>
                        </a>
                        <a href="{{ route('buku-induk.index') }}"
                           class="em-nav__link em-nav__link--sub {{ request()->routeIs('buku-induk.*') ? 'is-active' : '' }}">
                            <span class="em-nav__icon"><i class="fas fa-book-open"></i></span>
                            <span class="em-nav__text">Buku Induk</span>
                        </a>
                        <a href="{{ route('raport.index') }}"
                           class="em-nav__link em-nav__link--sub {{ request()->routeIs('raport.*') ? 'is-active' : '' }}">
                            <span class="em-nav__icon"><i class="fas fa-file-invoice"></i></span>
                            <span class="em-nav__text">Nilai Raport</span>
                        </a>
                    </div>
                </div>

                <a href="{{ route('jadwal.index') }}"
                   class="em-nav__link {{ request()->routeIs('jadwal.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Jadwal Pelajaran">
                    <span class="em-nav__icon"><i class="fas fa-calendar-days"></i></span>
                    <span class="em-nav__text">Jadwal Pelajaran</span>
                </a>
                @endif
                <a href="{{ route('absensi.index') }}"
                   class="em-nav__link {{ request()->routeIs('absensi.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Absensi Guru">
                    <span class="em-nav__icon"><i class="fas fa-fingerprint"></i></span>
                    <span class="em-nav__text">Absensi Guru</span>
                </a>
                @if($canManage)
                <a href="{{ route('arsip-akademik.index') }}"
                   class="em-nav__link {{ request()->routeIs('arsip-akademik.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Arsip Akademik">
                    <span class="em-nav__icon"><i class="fas fa-archive"></i></span>
                    <span class="em-nav__text">Arsip Akademik</span>
                </a>
                @endif
            </div>

            @if($canManage)
            <div class="em-nav__group">
                <span class="em-nav__label">Lainnya</span>
                <a href="{{ route('surat-masuk.index') }}"
                   class="em-nav__link {{ request()->routeIs('surat-masuk.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Surat Masuk">
                    <span class="em-nav__icon"><i class="fas fa-envelope-open-text"></i></span>
                    <span class="em-nav__text">Surat Masuk</span>
                </a>
                <a href="{{ route('surat-keluar.index') }}"
                   class="em-nav__link {{ request()->routeIs('surat-keluar.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Surat Keluar">
                    <span class="em-nav__icon"><i class="fas fa-paper-plane"></i></span>
                    <span class="em-nav__text">Surat Keluar</span>
                </a>
                <a href="{{ route('template-surat.index') }}"
                   class="em-nav__link {{ request()->routeIs('template-surat.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Template Surat">
                    <span class="em-nav__icon"><i class="fas fa-file-contract"></i></span>
                    <span class="em-nav__text">Template Surat</span>
                </a>
                <a href="{{ route('tasks.index') }}"
                   class="em-nav__link {{ request()->routeIs('tasks.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Manajemen Tugas TU">
                    <span class="em-nav__icon"><i class="fas fa-tasks"></i></span>
                    <span class="em-nav__text">Tugas TU</span>
                </a>
                <a href="{{ route('sarana.index') }}"
                   class="em-nav__link {{ request()->routeIs('sarana.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Sarana dan Peminjaman">
                    <span class="em-nav__icon"><i class="fas fa-warehouse"></i></span>
                    <span class="em-nav__text">Sarana</span>
                </a>
                <a href="{{ route('kategori-sarana.index') }}"
                   class="em-nav__link {{ request()->routeIs('kategori-sarana.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Kategori Sarana">
                    <span class="em-nav__icon"><i class="fas fa-tags"></i></span>
                    <span class="em-nav__text">Kategori Sarana</span>
                </a>
                @if(auth()->user()?->role === 'admin')
                <a href="{{ route('users.index') }}"
                   class="em-nav__link {{ request()->routeIs('users.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Manajemen Pengguna">
                    <span class="em-nav__icon"><i class="fas fa-user-cog"></i></span>
                    <span class="em-nav__text">Pengguna</span>
                </a>
                <a href="{{ route('pengaturan.edit') }}"
                   class="em-nav__link {{ request()->routeIs('pengaturan.*') ? 'is-active' : '' }}"
                   data-bs-toggle="tooltip" data-bs-placement="right" title="Pengaturan Madrasah">
                    <span class="em-nav__icon"><i class="fas fa-cog"></i></span>
                    <span class="em-nav__text">Pengaturan</span>
                </a>
                @endif
            </div>
            @endif

        </nav>
    </div>

    {{-- ── Footer: User info + Logout ── --}}
    <div class="em-sidebar__footer">
        <div class="em-user-info">
            <div class="em-user-avatar">
                <span>{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</span>
            </div>
            <div class="em-user-details">
                <span class="em-user-name">{{ Auth::user()->name ?? 'Admin' }}</span>
                <span class="em-user-role">{{ ucfirst(str_replace('_', ' ', Auth::user()->role ?? 'Admin')) }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="em-logout-form">
                @csrf
                <button type="submit" class="em-logout-btn" title="Keluar" aria-label="Keluar dari akun">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
        <div class="em-sidebar__version">e-Madrasah v2.0</div>
    </div>

</aside>

{{-- Bar atas ponsel (< 992px): tombol menu + nama aplikasi, tidak menutupi konten --}}
<header class="em-topbar d-lg-none">
    <button type="button" class="em-mobile-menu-btn" id="sidebarToggleMobile" aria-label="Buka menu">
        <i class="fas fa-bars"></i>
    </button>
    <span class="em-topbar__title">e-Madrasah</span>
</header>
