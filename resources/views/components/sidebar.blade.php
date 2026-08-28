@php
    $user = Auth::user();
    $can = fn (string $permission): bool => $user?->hasPermission($permission) ?? false;
    $canAny = fn (array $permissions): bool => collect($permissions)->contains(fn ($permission) => $can($permission));

    $groups = [
        'Dashboard' => [
            ['label' => 'Dashboard', 'route' => 'dashboard', 'active' => 'dashboard', 'icon' => 'fa-gauge-high', 'permission' => 'dashboard.view'],
        ],
        'Master Data' => [
            ['label' => 'Guru', 'route' => 'guru.index', 'active' => 'guru.*', 'icon' => 'fa-chalkboard-user', 'permission' => 'guru.view'],
            ['label' => 'Kelas', 'route' => 'kelas.index', 'active' => 'kelas.*', 'icon' => 'fa-door-open', 'permission' => 'kelas.view'],
            ['label' => 'Mata Pelajaran', 'route' => 'mapel.index', 'active' => 'mapel.*', 'icon' => 'fa-book-open', 'permission' => 'mapel.view'],
            ['label' => 'Jam Pelajaran', 'route' => 'jam-pelajaran.index', 'active' => 'jam-pelajaran.*', 'icon' => 'fa-clock', 'permission' => 'jam_pelajaran.view'],
            ['label' => 'Tahun Pelajaran', 'route' => 'tahun-pelajaran.index', 'active' => 'tahun-pelajaran.*', 'icon' => 'fa-calendar', 'permission' => 'tahun_pelajaran.view'],
        ],
        'Akademik' => [
            ['label' => 'Siswa', 'route' => 'siswa.index', 'active' => 'siswa.*', 'icon' => 'fa-users', 'permission' => 'siswa.view'],
            ['label' => 'Buku Induk', 'route' => 'buku-induk.index', 'active' => 'buku-induk.*', 'icon' => 'fa-book', 'permission' => 'buku_induk.view'],
            ['label' => 'Raport', 'route' => 'raport.index', 'active' => 'raport.*', 'icon' => 'fa-file-invoice', 'permission' => 'raport.view'],
            ['label' => 'Jadwal', 'route' => 'jadwal.index', 'active' => 'jadwal.*', 'icon' => 'fa-calendar-days', 'permission' => 'jadwal.view'],
            ['label' => 'Absensi Guru', 'route' => 'absensi.index', 'active' => 'absensi.*', 'icon' => 'fa-fingerprint', 'permission' => 'absensi.view'],
            ['label' => 'Arsip Akademik', 'route' => 'arsip-akademik.index', 'active' => 'arsip-akademik.*', 'icon' => 'fa-archive', 'permission' => 'arsip_akademik.view'],
        ],
        'Administrasi' => [
            ['label' => 'Surat Masuk', 'route' => 'surat-masuk.index', 'active' => 'surat-masuk.*', 'icon' => 'fa-envelope-open-text', 'permission' => 'surat_masuk.view'],
            ['label' => 'Surat Keluar', 'route' => 'surat-keluar.index', 'active' => 'surat-keluar.*', 'icon' => 'fa-paper-plane', 'permission' => 'surat_keluar.view'],
            ['label' => 'Template Surat', 'route' => 'template-surat.index', 'active' => 'template-surat.*', 'icon' => 'fa-file-contract', 'permission' => 'template_surat.view'],
            ['label' => 'Tugas TU', 'route' => 'tasks.index', 'active' => 'tasks.*', 'icon' => 'fa-list-check', 'permission' => 'tasks.view'],
        ],
        'Sarpras' => [
            ['label' => 'Sarana', 'route' => 'sarana.index', 'active' => 'sarana.*', 'icon' => 'fa-warehouse', 'permission' => 'sarana.view'],
            ['label' => 'Kategori Sarana', 'route' => 'kategori-sarana.index', 'active' => 'kategori-sarana.*', 'icon' => 'fa-tags', 'permission' => 'kategori_sarana.view'],
        ],
        'Sistem' => [
            ['label' => 'Pengguna', 'route' => 'users.index', 'active' => 'users.*', 'icon' => 'fa-user-cog', 'permission' => 'users.view'],
        ],
    ];
@endphp

<aside class="em-sidebar" id="emSidebar">
    <div class="em-sidebar__header">
        <div class="em-brand">
            <div class="em-brand__logo">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" width="24" height="24">
            </div>
            <div class="em-brand__text">
                <span class="em-brand__title">e-Madrasah</span>
                <span class="em-brand__sub">MTs Al-Ihsan</span>
            </div>
        </div>
        <button class="em-sidebar-toggle" id="sidebarToggleDesktop" aria-label="Ciutkan sidebar">
            <i class="fas fa-chevron-left"></i>
        </button>
    </div>

    <div class="em-sidebar__inner">
        <nav class="em-nav" aria-label="Navigasi utama">
            @foreach ($groups as $group => $items)
                @continue(! $canAny(collect($items)->pluck('permission')->all()))

                <div class="em-nav__group">
                    <span class="em-nav__label">{{ $group }}</span>

                    @foreach ($items as $item)
                        @continue(! $can($item['permission']))

                        <a href="{{ route($item['route']) }}"
                           class="em-nav__link {{ request()->routeIs($item['active']) ? 'is-active' : '' }}"
                           @if(request()->routeIs($item['active'])) aria-current="page" @endif
                           data-bs-toggle="tooltip" data-bs-placement="right" title="{{ $item['label'] }}">
                            <span class="em-nav__icon"><i class="fas {{ $item['icon'] }}"></i></span>
                            <span class="em-nav__text">{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
    </div>

    <div class="em-sidebar__footer">
        <div class="em-user-info">
            <div class="em-user-avatar">
                <span>{{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}</span>
            </div>
            <div class="em-user-details">
                <span class="em-user-name">{{ $user->name ?? 'Admin' }}</span>
                <span class="em-user-role">{{ str_replace('_', ' ', $user->role ?? 'admin') }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="em-logout-form">
                @csrf
                <button type="submit" class="em-logout-btn" title="Logout" aria-label="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
        </div>
        <div class="em-sidebar__version">e-Madrasah v2.0</div>
    </div>
</aside>

<button class="em-mobile-menu-btn d-lg-none" id="sidebarToggleMobile" aria-label="Buka menu">
    <i class="fas fa-bars"></i>
</button>
