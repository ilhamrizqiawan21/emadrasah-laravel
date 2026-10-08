@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- ── Greeting Banner ── --}}
<div class="dash-greeting d-flex align-items-center justify-content-between flex-wrap gap-3">
    <div>
        @php
            $jam = now()->hour;
        @endphp
        <div class="dash-greeting__label">Selamat {{ match (true) { $jam >= 4 && $jam < 11 => 'pagi', $jam >= 11 && $jam < 15 => 'siang', $jam >= 15 && $jam < 18 => 'sore', default => 'malam' } }}</div>
        <div class="dash-greeting__name">{{ Auth::user()->name ?? 'Admin' }} </div>
        <div class="dash-greeting__sub">Berikut ringkasan aktivitas madrasah hari ini.</div>
        <div class="dash-greeting__date">
            <i class="fas fa-calendar-days"></i>
            {{ now()->translatedFormat('l, d F Y') }}
        </div>
    </div>
    <div class="d-flex flex-column align-items-end gap-2">
        <div class="dash-greeting__badge">
            <i class="fas fa-school"></i>
            {{ $madrasah->nama }}
        </div>
    </div>
</div>

{{-- ── Row 1: 4 Stat Cards Utama ── --}}
<span class="em-section-label">Statistik Utama</span>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="em-stat" style="--em-stat-color:var(--em-green-700);">
            <div class="em-stat__icon"><i class="fas fa-user-graduate"></i></div>
            <div class="em-stat__body">
                <div class="em-stat__label">Total Siswa</div>
                <div class="em-stat__value">{{ $totalSiswa }}</div>
                <div class="em-stat__sub">Siswa terdaftar</div>
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

{{-- ── Row 2: Stat Cards Sekunder + Kehadiran ── --}}
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
    {{-- Kehadiran hari ini: progress bar mini --}}
    <div class="col-6 col-md-3">
        <div class="em-stat" style="--em-stat-color:#1a7a52; flex-direction:column; align-items:flex-start; gap:10px;">
            <div class="d-flex align-items-center justify-content-between w-100">
                <div>
                    <div class="em-stat__label" style="margin-bottom:2px;white-space:normal;">Kehadiran Hari Ini</div>
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
                    <span style="font-size:.75rem;color:var(--em-text-muted);">{{ $guruHadirHariIni }} hadir</span>
                    <span style="font-size:.75rem;color:var(--em-text-muted);">{{ $totalGuru - $guruHadirHariIni }} tidak hadir</span>
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
                <a href="{{ route('tasks.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.75rem;padding:3px 10px;border-radius:6px;">
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
                        <i class="fas fa-calendar-xmark" style="font-size:.75rem;"></i>
                        {{ $task->deadline ? $task->deadline->translatedFormat('d M Y') : 'Tanpa deadline' }}
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
                <a href="{{ route('surat-masuk.index') }}" class="btn btn-sm btn-outline-secondary" style="font-size:.75rem;padding:3px 10px;border-radius:6px;">
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
                        {{ $surat->tanggal_terima->translatedFormat('d M Y') }}
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
<script>
document.addEventListener('DOMContentLoaded', async function () {
    const kehadiran = @json($kehadiran);

    const ctx = document.getElementById('attendanceChart').getContext('2d');

    await window.loadChart();

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