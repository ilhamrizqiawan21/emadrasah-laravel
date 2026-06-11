@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
/* ── Dashboard-specific styles ── */

/* Greeting banner */
.dash-greeting {
    background: linear-gradient(135deg, #052e1c 0%, #1a7a52 60%, #22a06b 100%);
    border-radius: var(--em-r-xl);
    padding: 26px 30px;
    margin-bottom: 24px;
    position: relative;
    overflow: hidden;
}
.dash-greeting::before {
    content: '';
    position: absolute;
    right: -40px; top: -40px;
    width: 200px; height: 200px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
}
.dash-greeting::after {
    content: '';
    position: absolute;
    right: 60px; bottom: -60px;
    width: 160px; height: 160px;
    border-radius: 50%;
    background: rgba(255,255,255,.04);
}
.dash-greeting__label {
    font-size: .7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1.2px;
    color: rgba(255,255,255,.55);
    margin-bottom: 4px;
}
.dash-greeting__name {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.45rem;
    font-weight: 800;
    color: #fff;
    margin-bottom: 2px;
    line-height: 1.2;
}
.dash-greeting__sub {
    font-size: .8rem;
    color: rgba(255,255,255,.6);
}
.dash-greeting__date {
    font-size: .75rem;
    color: rgba(255,255,255,.5);
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.dash-greeting__badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 5px 12px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    border-radius: var(--em-r-full);
    color: rgba(255,255,255,.85);
    font-size: .73rem;
    font-weight: 600;
    backdrop-filter: blur(4px);
    white-space: nowrap;
}

/* Stat cards */
.em-stat {
    background: #fff;
    border-radius: var(--em-r-lg);
    border: 1px solid var(--em-border);
    padding: 18px 20px;
    display: flex;
    align-items: center;
    gap: 14px;
    transition: transform .2s var(--em-ease), box-shadow .2s var(--em-ease);
    position: relative;
    overflow: hidden;
}
.em-stat::after {
    content: '';
    position: absolute;
    bottom: 0; left: 0; right: 0;
    height: 3px;
    background: var(--em-stat-color, var(--em-primary));
    opacity: 0;
    transition: opacity .2s;
}
.em-stat:hover { transform: translateY(-4px); box-shadow: var(--em-shadow-md); }
.em-stat:hover::after { opacity: 1; }

.em-stat__icon {
    width: 46px; height: 46px;
    border-radius: var(--em-r-lg);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.05rem;
    flex-shrink: 0;
    color: #fff;
    background: var(--em-stat-color, var(--em-primary));
}
.em-stat__body { flex: 1; min-width: 0; }
.em-stat__label {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .7px;
    color: var(--em-text-muted);
    margin-bottom: 2px;
    white-space: nowrap;
}
.em-stat__value {
    font-family: 'Plus Jakarta Sans', sans-serif;
    font-size: 1.75rem;
    font-weight: 800;
    color: var(--em-gray-900);
    line-height: 1.1;
}
.em-stat__sub {
    font-size: .7rem;
    color: var(--em-text-muted);
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* Chart card */
.em-chart-card {
    background: #fff;
    border-radius: var(--em-r-lg);
    border: 1px solid var(--em-border);
    overflow: hidden;
}
.em-chart-card__header {
    padding: 14px 18px;
    border-bottom: 1px solid var(--em-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}
.em-chart-card__title {
    font-size: .86rem;
    font-weight: 700;
    color: var(--em-gray-900);
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
}
.em-chart-card__body { padding: 16px 18px; }

/* Legend pills */
.chart-legend { display: flex; gap: 12px; flex-wrap: wrap; }
.chart-legend__item {
    display: flex; align-items: center; gap: 5px;
    font-size: .72rem; font-weight: 600; color: var(--em-gray-600);
}
.chart-legend__dot {
    width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0;
}

/* Activity cards */
.em-activity-card {
    background: #fff;
    border-radius: var(--em-r-lg);
    border: 1px solid var(--em-border);
    overflow: hidden;
    height: 100%;
}
.em-activity-card__header {
    padding: 14px 18px;
    border-bottom: 1px solid var(--em-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.em-activity-card__title {
    font-size: .86rem;
    font-weight: 700;
    color: var(--em-gray-900);
    display: flex;
    align-items: center;
    gap: 7px;
    margin: 0;
}
.em-activity-item {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 12px 18px;
    border-bottom: 1px solid var(--em-gray-100);
    transition: background .13s;
}
.em-activity-item:last-child { border-bottom: none; }
.em-activity-item:hover { background: var(--em-green-50); }
.em-activity-item__icon {
    width: 32px; height: 32px;
    border-radius: var(--em-r-md);
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem;
    flex-shrink: 0;
}
.em-activity-item__body { flex: 1; min-width: 0; }
.em-activity-item__title {
    font-size: .82rem;
    font-weight: 600;
    color: var(--em-gray-800);
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.em-activity-item__meta {
    font-size: .72rem;
    color: var(--em-text-muted);
    display: flex;
    align-items: center;
    gap: 6px;
    flex-wrap: wrap;
}
.em-activity-empty {
    padding: 28px 18px;
    text-align: center;
    color: var(--em-text-muted);
    font-size: .82rem;
}
.em-activity-empty i { font-size: 1.4rem; display: block; margin-bottom: 6px; opacity: .35; }

/* Priority badge */
.em-prio {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 2px 8px; border-radius: var(--em-r-sm);
    font-size: .67rem; font-weight: 700; flex-shrink: 0;
}
.em-prio-tinggi  { background: #fee2e2; color: #991b1b; }
.em-prio-sedang  { background: #fef3c7; color: #92400e; }
.em-prio-rendah  { background: #dbeafe; color: #1e40af; }

/* Status badge surat */
.em-status {
    display: inline-block;
    padding: 2px 8px; border-radius: var(--em-r-sm);
    font-size: .67rem; font-weight: 700;
}
.em-status-diterima  { background: #dbeafe; color: #1e40af; }
.em-status-diproses  { background: #fef3c7; color: #92400e; }
.em-status-selesai   { background: #d1fae5; color: #065f46; }

/* Quick actions */
.em-quick-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
}
.em-quick-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 14px 8px;
    border-radius: var(--em-r-lg);
    border: 1px solid var(--em-border);
    background: #fff;
    color: var(--em-gray-600);
    font-size: .75rem;
    font-weight: 600;
    text-align: center;
    cursor: pointer;
    text-decoration: none;
    transition: all .18s var(--em-ease);
    line-height: 1.3;
}
.em-quick-btn:hover {
    background: var(--em-green-50);
    border-color: var(--em-green-200);
    color: var(--em-primary);
    transform: translateY(-3px);
    box-shadow: 0 4px 14px rgba(26,122,82,.12);
}
.em-quick-btn i {
    font-size: 1.2rem;
    width: 36px; height: 36px;
    border-radius: var(--em-r-md);
    display: flex; align-items: center; justify-content: center;
    background: var(--em-gray-100);
    transition: background .18s;
}
.em-quick-btn:hover i { background: var(--em-green-100); color: var(--em-primary); }

/* Section label */
.em-section-label {
    font-size: .68rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .9px;
    color: var(--em-gray-400);
    margin-bottom: 10px;
    display: block;
}

@media (max-width: 991.98px) {
    .dash-greeting { padding: 20px; }
    .dash-greeting__name { font-size: 1.2rem; }
    .em-stat__value { font-size: 1.5rem; }
    .em-quick-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 575.98px) {
    .em-stat { padding: 14px; }
    .em-stat__icon { width: 38px; height: 38px; font-size: .9rem; }
    .em-quick-grid { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush

@section('content')

{{-- ── Greeting Banner ── --}}
<div class="dash-greeting d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        <div class="dash-greeting__label">Selamat datang kembali</div>
        <div class="dash-greeting__name">{{ Auth::user()->name ?? 'Admin' }} </div>
        <div class="dash-greeting__sub">Berikut ringkasan aktivitas madrasah hari ini.</div>
        <div class="dash-greeting__date">
            <i class="fas fa-calendar-days"></i>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>
    <div class="d-flex flex-column align-items-end gap-2">
        <div class="dash-greeting__badge">
            <i class="fas fa-circle-dot" style="color:#34d399; font-size:.6rem;"></i>
            Sistem Online
        </div>
        <div class="dash-greeting__badge">
            <i class="fas fa-school"></i>
            MTs Al-Ihsan Batujajar
        </div>
    </div>
</div>

{{-- ── Row 1: 4 Stat Cards Utama ── --}}
<span class="em-section-label">Statistik Utama</span>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="em-stat" style="--em-stat-color:#1a7a52;">
            <div class="em-stat__icon"><i class="fas fa-user-graduate"></i></div>
            <div class="em-stat__body">
                <div class="em-stat__label">Total Siswa</div>
                <div class="em-stat__value">{{ $totalSiswa }}</div>
                <div class="em-stat__sub">Siswa terdaftar</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="em-stat" style="--em-stat-color:#0ea5e9;">
            <div class="em-stat__icon"><i class="fas fa-book-bookmark"></i></div>
            <div class="em-stat__body">
                <div class="em-stat__label">Buku Induk</div>
                <div class="em-stat__value">{{ $persenLengkap }}%</div>
                <div class="em-stat__sub">Data sudah lengkap</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="em-stat" style="--em-stat-color:#3b82f6;">
            <div class="em-stat__icon"><i class="fas fa-door-open"></i></div>
            <div class="em-stat__body">
                <div class="em-stat__label">Total Kelas</div>
                <div class="em-stat__value">{{ $totalKelas }}</div>
                <div class="em-stat__sub">Kelas aktif semester ini</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="em-stat" style="--em-stat-color:#f59e0b;">
            <div class="em-stat__icon"><i class="fas fa-list-check"></i></div>
            <div class="em-stat__body">
                <div class="em-stat__label">Tugas Pending</div>
                <div class="em-stat__value">{{ $taskPending }}</div>
                <div class="em-stat__sub">Belum diselesaikan</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="em-stat" style="--em-stat-color:#8b5cf6;">
            <div class="em-stat__icon"><i class="fas fa-envelope-open-text"></i></div>
            <div class="em-stat__body">
                <div class="em-stat__label">Surat Masuk</div>
                <div class="em-stat__value">{{ $suratMasukBulanIni }}</div>
                <div class="em-stat__sub">Bulan {{ now()->translatedFormat('F') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ── Row 2: 2 Stat Cards Sekunder ── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="em-stat" style="--em-stat-color:#10b981;">
            <div class="em-stat__icon"><i class="fas fa-user-check"></i></div>
            <div class="em-stat__body">
                <div class="em-stat__label">Guru Hadir</div>
                <div class="em-stat__value">{{ $guruHadirHariIni }}</div>
                <div class="em-stat__sub">Dari {{ $totalGuru }} guru</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="em-stat" style="--em-stat-color:#ef4444;">
            <div class="em-stat__icon"><i class="fas fa-triangle-exclamation"></i></div>
            <div class="em-stat__body">
                <div class="em-stat__label">Sarana Rusak</div>
                <div class="em-stat__value">{{ $saranaRusak }}</div>
                <div class="em-stat__sub">Perlu perhatian</div>
            </div>
        </div>
    </div>
    {{-- Kehadiran hari ini: progress bar mini --}}
    <div class="col-12 col-md-6">
        <div class="em-stat" style="--em-stat-color:#1a7a52; flex-direction:column; align-items:flex-start; gap:10px;">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div>
                    <div class="em-stat__label" style="margin-bottom:2px;">Tingkat Kehadiran Hari Ini</div>
                    @php
                        $pct = $totalGuru > 0 ? round(($guruHadirHariIni / $totalGuru) * 100) : 0;
                    @endphp
                    <span style="font-family:'Plus Jakarta Sans',sans-serif;font-size:1.5rem;font-weight:800;color:var(--em-gray-900);">{{ $pct }}%</span>
                </div>
                <div class="em-stat__icon" style="background:#1a7a52;"><i class="fas fa-gauge-high"></i></div>
            </div>
            <div class="w-100">
                <div style="height:7px;background:var(--em-gray-100);border-radius:99px;overflow:hidden;">
                    <div style="height:100%;width:{{ $pct }}%;background:linear-gradient(90deg,#22a06b,#1a7a52);border-radius:99px;transition:width .6s ease;"></div>
                </div>
                <div style="display:flex;justify-content:space-between;margin-top:4px;">
                    <span style="font-size:.68rem;color:var(--em-text-muted);">{{ $guruHadirHariIni }} hadir</span>
                    <span style="font-size:.68rem;color:var(--em-text-muted);">{{ $totalGuru - $guruHadirHariIni }} tidak hadir</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Row 3: Chart ── --}}
<span class="em-section-label">Grafik Kehadiran</span>
<div class="em-chart-card mb-4">
    <div class="em-chart-card__header">
        <h5 class="em-chart-card__title">
            <i class="fas fa-chart-area" style="color:var(--em-primary);"></i>
            Kehadiran Guru — 7 Hari Terakhir
        </h5>
        <div class="chart-legend">
            <div class="chart-legend__item">
                <div class="chart-legend__dot" style="background:#10b981;"></div> Hadir
            </div>
            <div class="chart-legend__item">
                <div class="chart-legend__dot" style="background:#ef4444;"></div> Tidak Hadir
            </div>
        </div>
    </div>
    <div class="em-chart-card__body">
        <canvas id="attendanceChart" style="max-height:210px;"></canvas>
    </div>
</div>

{{-- ── Row 4: Aksi Cepat ── --}}
<span class="em-section-label">Aksi Cepat</span>
<div class="em-quick-grid mb-4">
    <a href="{{ route('absensi.index') }}" class="em-quick-btn">
        <i class="fas fa-fingerprint"></i>
        Catat Absensi
    </a>
    <a href="{{ route('surat-masuk.create') }}" class="em-quick-btn">
        <i class="fas fa-envelope-open-text"></i>
        Surat Masuk Baru
    </a>
    <a href="{{ route('tasks.create') }}" class="em-quick-btn">
        <i class="fas fa-plus-circle"></i>
        Tambah Tugas
    </a>
    <a href="{{ route('sarana.index') }}" class="em-quick-btn">
        <i class="fas fa-boxes-stacked"></i>
        Cek Sarana
    </a>
</div>

{{-- ── Row 5: Tugas Pending + Surat Masuk Terbaru ── --}}
<span class="em-section-label">Aktivitas Terkini</span>
<div class="row g-3 mb-2">

    {{-- Tugas Pending --}}
    <div class="col-12 col-md-6">
        <div class="em-activity-card">
            <div class="em-activity-card__header">
                <h5 class="em-activity-card__title">
                    <i class="fas fa-list-check" style="color:#f59e0b;"></i>
                    Tugas Belum Selesai
                </h5>
                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;padding:3px 10px;border-radius:6px;">
                    Lihat Semua
                </a>
            </div>

            @forelse($pendingTasks ?? [] as $task)
            <div class="em-activity-item">
                <div class="em-activity-item__icon" style="background:{{ $task->prioritas === 'tinggi' ? '#fee2e2' : ($task->prioritas === 'sedang' ? '#fef3c7' : '#dbeafe') }};">
                    <i class="fas fa-circle-dot" style="color:{{ $task->prioritas === 'tinggi' ? '#dc2626' : ($task->prioritas === 'sedang' ? '#d97706' : '#2563eb') }};font-size:.75rem;"></i>
                </div>
                <div class="em-activity-item__body">
                    <div class="em-activity-item__title">{{ $task->judul }}</div>
                    <div class="em-activity-item__meta">
                        <i class="fas fa-calendar-xmark" style="font-size:.65rem;"></i>
                        {{ $task->deadline ? $task->deadline->format('d M Y') : 'Tanpa deadline' }}
                    </div>
                </div>
                <span class="em-prio em-prio-{{ $task->prioritas }}">
                    {{ ucfirst($task->prioritas) }}
                </span>
            </div>
            @empty
            <div class="em-activity-empty">
                <i class="fas fa-circle-check" style="color:#10b981;opacity:1;"></i>
                Semua tugas sudah selesai!
            </div>
            @endforelse
        </div>
    </div>

    {{-- Surat Masuk Terbaru --}}
    <div class="col-12 col-md-6">
        <div class="em-activity-card">
            <div class="em-activity-card__header">
                <h5 class="em-activity-card__title">
                    <i class="fas fa-envelope" style="color:#8b5cf6;"></i>
                    Surat Masuk Terbaru
                </h5>
                <a href="{{ route('surat-masuk.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.72rem;padding:3px 10px;border-radius:6px;">
                    Lihat Semua
                </a>
            </div>

            @forelse($recentSuratMasuk ?? [] as $surat)
            <div class="em-activity-item">
                <div class="em-activity-item__icon" style="background:#ede9fe;">
                    <i class="fas fa-file-lines" style="color:#7c3aed;font-size:.8rem;"></i>
                </div>
                <div class="em-activity-item__body">
                    <div class="em-activity-item__title">{{ $surat->asal_surat }}</div>
                    <div class="em-activity-item__meta">
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:160px;display:inline-block;">
                            {{ $surat->perihal }}
                        </span>
                        <span>·</span>
                        {{ $surat->tanggal_terima->format('d M Y') }}
                    </div>
                </div>
                @php
                    $statusClass = match($surat->status) {
                        'diproses' => 'em-status-diproses',
                        'selesai'  => 'em-status-selesai',
                        default    => 'em-status-diterima',
                    };
                @endphp
                <span class="em-status {{ $statusClass }}">
                    {{ ucfirst($surat->status) }}
                </span>
            </div>
            @empty
            <div class="em-activity-empty">
                <i class="fas fa-inbox"></i>
                Belum ada surat masuk.
            </div>
            @endforelse
        </div>
    </div>

</div>

@endSection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const kehadiran = @json($kehadiran);

    const ctx = document.getElementById('attendanceChart').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: kehadiran.map(d => d.tanggal),
            datasets: [
                {
                    label: 'Hadir',
                    data: kehadiran.map(d => d.hadir),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16,185,129,0.08)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.45,
                    fill: true,
                },
                {
                    label: 'Tidak Hadir',
                    data: kehadiran.map(d => d.tidak_hadir),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.06)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#ef4444',
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    tension: 0.45,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false }, // legend kustom pakai HTML di atas
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleColor: '#f9fafb',
                    bodyColor: '#d1d5db',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label: ctx => ` ${ctx.dataset.label}: ${ctx.parsed.y} guru`,
                    },
                },
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#9ca3af' },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        stepSize: 1,
                        font: { size: 11 },
                        color: '#9ca3af',
                        callback: v => Number.isInteger(v) ? v : '',
                    },
                },
            },
        },
    });
});
</script>
@endpush