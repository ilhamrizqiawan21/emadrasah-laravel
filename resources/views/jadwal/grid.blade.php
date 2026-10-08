@extends('layouts.app')

@section('title', 'Input Jadwal – Mode Grid')

@section('content')

{{-- Header Halaman --}}
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h2 class="em-page-title mb-1">
            <i class="fas fa-table-cells text-primary me-2"></i>Input Jadwal – Grid
        </h2>
        <p class="text-muted mb-0 small">
            Ketik kode guru pada sel &middot; <kbd class="px-1 text-dark bg-light border">Enter</kbd> / <kbd class="px-1 text-dark bg-light border">Panah</kbd> pindah sel &middot; <kbd class="px-1 text-dark bg-light border">Del</kbd> hapus &middot; Otomatis cek bentrok jam
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('jadwal.index') }}" class="btn btn-light border">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#guruListModal">
            <i class="fas fa-id-card-alt me-1"></i> Daftar Guru &amp; JP (<span id="totalJpCount">0</span>)
        </button>
        <button class="btn btn-primary px-3 shadow-sm" id="btnSaveAll" disabled>
            <i class="fas fa-cloud-arrow-up me-1"></i>
            <span>Simpan Semua</span>
            <span class="badge bg-warning text-dark ms-1" id="changesCount" style="display:none">0</span>
        </button>
    </div>
</div>

{{-- Bar Kontrol: Filter Hari & Legend --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        {{-- Filter Hari Tab --}}
        <div class="d-flex align-items-center gap-1" id="hariFilterGroup">
            <span class="small text-muted fw-bold me-2"><i class="fas fa-filter me-1"></i>Hari:</span>
            <button type="button" class="btn btn-sm btn-primary active-filter filter-hari-btn" data-hari="all">Semua</button>
            @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $h)
                <button type="button" class="btn btn-sm btn-light border filter-hari-btn" data-hari="{{ $h }}">{{ $h }}</button>
            @endforeach
        </div>

        {{-- Legend --}}
        <div class="d-flex flex-wrap align-items-center gap-3 small text-muted">
            <span class="d-flex align-items-center gap-1">
                <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:#dcfce7;border:1px solid #86efac;"></span> Terisi
            </span>
            <span class="d-flex align-items-center gap-1">
                <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:#fef3c7;border:1px solid #fde68a;"></span> Berubah
            </span>
            <span class="d-flex align-items-center gap-1">
                <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:#fee2e2;border:1px solid #fca5a5;"></span> Bentrok / Tidak Valid
            </span>
            <span class="d-flex align-items-center gap-1">
                <span class="d-inline-block rounded-circle" style="width:10px;height:10px;background:#ffffff;border:1px solid #e2e8f0;"></span> Kosong
            </span>
        </div>
    </div>
</div>

{{-- Grid Container --}}
<div class="card border-0 shadow-sm overflow-hidden mb-5">
    <div class="grid-scroll-shell" style="max-height: calc(100vh - 280px); overflow: auto;">
        <table class="grid-table w-100" id="mainGridTable">
            <thead>
                <tr>
                    <th class="col-header-jam sticky-col-head">Sesi &amp; Waktu</th>
                    @foreach($kelas as $k)
                        <th class="col-header text-center" data-kelas-id="{{ $k->id }}">
                            <div class="fw-bold">{{ $k->nama_kelas }}</div>
                            <small class="text-muted fw-normal" style="font-size:0.7rem;">Tingkat {{ $k->tingkat }}</small>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @php $currentHari = null; @endphp

                @foreach($jamPelajaran as $jam)
                    {{-- Header Pemisah Hari --}}
                    @if($jam->hari !== $currentHari)
                        @php $currentHari = $jam->hari; @endphp
                        <tr class="day-row" data-day-group="{{ $jam->hari }}">
                            <td colspan="{{ count($kelas) + 1 }}" class="py-2 px-3 fw-bold bg-light border-bottom text-uppercase letter-spacing-1 text-primary">
                                <i class="fas fa-calendar-day me-1"></i> {{ $jam->hari }}
                            </td>
                        </tr>
                    @endif

                    <tr class="sesi-row" data-day="{{ $jam->hari }}" data-sesi-id="{{ $jam->id }}">
                        {{-- Kolom Sesi & Waktu (Sticky Left) --}}
                        <td class="sesi-cell sticky-col bg-white text-center">
                            <span class="fw-bold text-dark d-block" style="font-size:0.85rem;">Sesi {{ $jam->sesi_ke }}</span>
                            <span class="text-muted d-block" style="font-size:0.75rem;">
                                {{ substr($jam->jam_mulai,0,5) }} – {{ substr($jam->jam_selesai,0,5) }}
                            </span>
                        </td>

                        {{-- Kolom Input per Kelas --}}
                        @foreach($kelas as $k)
                            @php
                                $key = $k->id . '_' . $jam->hari . '_' . $jam->id;
                                $existing = $jadwalGrid[$key] ?? null;
                                $kodeVal = $existing ? $existing['guru_kode'] : '';
                                $mapelId = $existing ? $existing['mapel_id'] : '';
                                $mapelNama = $existing ? ($existing['mapel_nama'] ?? '') : '';
                            @endphp
                            <td class="input-cell p-0 position-relative text-center"
                                data-kelas-id="{{ $k->id }}"
                                data-hari="{{ $jam->hari }}"
                                data-jam-id="{{ $jam->id }}">

                                <div class="cell-wrapper h-100 d-flex flex-column justify-content-center">
                                    <input type="text"
                                           class="grid-input {{ $kodeVal ? 'has-value' : '' }}"
                                           data-kelas-id="{{ $k->id }}"
                                           data-jam-id="{{ $jam->id }}"
                                           data-hari="{{ $jam->hari }}"
                                           data-jam-mulai="{{ $jam->jam_mulai }}"
                                           data-jam-selesai="{{ $jam->jam_selesai }}"
                                           data-jadwal-id="{{ $existing['jadwal_id'] ?? '' }}"
                                           data-guru-id="{{ $existing['guru_id'] ?? '' }}"
                                           data-mapel-id="{{ $mapelId }}"
                                           data-original="{{ $kodeVal }}"
                                           data-original-mapel="{{ $mapelId }}"
                                           value="{{ $kodeVal }}"
                                           placeholder="—"
                                           autocomplete="off">

                                    <span class="cell-mapel-tag" style="{{ $mapelNama ? '' : 'display:none;' }}">
                                        {{ Str::limit($mapelNama, 10) }}
                                    </span>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Floating Action Bar di Bawah --}}
<div class="status-bar d-flex align-items-center justify-content-between px-4 py-2 border-top bg-white shadow-lg">
    <div class="d-flex align-items-center gap-2">
        <span class="status-dot rounded-circle d-inline-block" id="statusDot" style="width:10px;height:10px;background:#94a3b8;"></span>
        <span class="small fw-semibold text-secondary" id="statusMsg">Siap — klik sel atau gunakan tombol panah keyboard</span>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-light border" onclick="window.scrollTo({top:0, behavior:'smooth'})">
            <i class="fas fa-arrow-up me-1"></i> Ke Atas
        </button>
        <button class="btn btn-sm btn-primary px-3" id="btnSaveAllBottom" disabled>
            <i class="fas fa-cloud-arrow-up me-1"></i> Simpan Semua
        </button>
    </div>
</div>

{{-- Tooltip Guru & Mapel Mini Popover --}}
<div class="guru-tooltip" id="guruTooltip" style="display:none; position:fixed; z-index:1060;">
    <div class="card shadow-lg border-0" style="min-width:220px; max-width:280px; border-radius:12px;">
        <div class="card-body p-2">
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge bg-primary px-2 py-1" id="ttKode">-</span>
                <strong class="text-dark small text-truncate" id="ttName">Pilih Guru</strong>
            </div>
            <div class="text-muted small mb-2" id="ttSub" style="font-size:0.75rem;"></div>

            {{-- Pemilih Mapel Cepat --}}
            <div class="border-top pt-2 mt-1">
                <label class="form-label mb-1 text-muted" style="font-size:0.7rem; font-weight:600;">MATA PELAJARAN:</label>
                <select class="form-select form-select-sm py-0" id="ttMapelSelect" style="font-size:0.75rem;">
                    @foreach($mapels as $m)
                        <option value="{{ $m->id }}">{{ $m->nama_mapel }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>
</div>

{{-- Modal Daftar Guru & Live Beban Mengajar --}}
<div class="modal fade" id="guruListModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius:16px;">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-id-card-alt me-2"></i>Daftar Guru &amp; Beban Mengajar (JP)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="row g-2 mb-3">
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="searchGuru" class="form-control border-start-0" placeholder="Cari kode, nama, atau mapel…">
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100 h-100" id="btnResetHighlight">
                            <i class="fas fa-eye-slash me-1"></i> Bersihkan Sorotan
                        </button>
                    </div>
                </div>

                <div class="table-responsive" style="max-height:420px;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width:70px">Kode</th>
                                <th>Nama Guru</th>
                                <th>Bidang Studi</th>
                                <th class="text-center" style="width:90px">Total JP</th>
                                <th style="width:110px" class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="guruListBody">
                            @foreach($gurus as $g)
                            <tr class="guru-modal-row"
                                data-id="{{ $g->id }}"
                                data-kode="{{ strtolower($g->kode) }}"
                                data-nama="{{ strtolower($g->nama) }}"
                                data-bidang="{{ strtolower($g->bidang_studi ?? '') }}">
                                <td><span class="badge bg-light text-primary border fw-bold">{{ $g->kode }}</span></td>
                                <td class="fw-semibold text-dark">{{ $g->nama }}</td>
                                <td class="text-muted small">{{ $g->bidang_studi ?? '–' }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success bg-opacity-10 text-success fw-bold jp-counter" id="jpCount_{{ $g->id }}">0</span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-xs btn-outline-primary me-1 py-1 px-2"
                                            onclick="highlightGuru({{ Js::from($g->kode) }})"
                                            title="Sorot jadwal guru ini di grid">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-xs btn-primary py-1 px-2"
                                            onclick="insertKode({{ Js::from($g->kode) }})"
                                            title="Masukkan kode ke sel aktif">
                                        <i class="fas fa-plus"></i> Isi
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 justify-content-between">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i>Klik tombol <strong>Sorot</strong> untuk melihat persebaran jam guru tersebut di seluruh kelas.
                </small>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<style>
/* CSS Tambahan khusus Grid Jadwal */
.sticky-col-head {
    position: sticky;
    top: 0;
    left: 0;
    z-index: 30;
    background: #f8fafc !important;
}
.sticky-col {
    position: sticky;
    left: 0;
    z-index: 20;
    background: #ffffff;
    border-right: 2px solid #e2e8f0;
}
.cell-wrapper {
    min-height: 52px;
    padding: 2px;
}
.grid-input {
    border: none;
    background: transparent;
    text-align: center;
    font-weight: 700;
    font-size: 0.95rem;
    color: #1e293b;
    width: 100%;
    outline: none;
    border-radius: 6px;
    transition: all 0.15s;
}
.grid-input:focus {
    background: #ffffff;
    box-shadow: 0 0 0 2px var(--em-green-700);
    z-index: 10;
}
.grid-input.has-value {
    background: #f0fdf4;
    color: #15803d;
}
.grid-input.changed {
    background: #fef3c7 !important;
    color: #b45309 !important;
    box-shadow: inset 0 0 0 1px #f59e0b;
}
.grid-input.conflict {
    background: #fee2e2 !important;
    color: #b91c1c !important;
    box-shadow: inset 0 0 0 2px #ef4444 !important;
    animation: pulseConflict 1.5s infinite;
}
.grid-input.highlighted {
    background: #fef08a !important;
    color: #854d0e !important;
    box-shadow: 0 0 0 2px #eab308 !important;
}
@keyframes pulseConflict {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.7; }
}
.cell-mapel-tag {
    font-size: 0.65rem;
    color: #64748b;
    display: block;
    line-height: 1;
    margin-top: -2px;
    pointer-events: none;
}
.status-bar {
    position: fixed;
    bottom: 0;
    left: var(--em-sidebar-w);
    right: 0;
    height: 52px;
    z-index: 1020;
    transition: left var(--em-dur) var(--em-ease);
}
.em-sidebar.is-collapsed ~ .em-main .status-bar,
.em-sidebar.is-collapsed ~ .status-bar {
    left: var(--em-sidebar-collapsed-w);
}
@media (max-width: 991.98px) {
    .status-bar { left: 0 !important; }
}
</style>

<script>
/* ─── Master Data ─── */
const guruData   = @json($gurus);
const mapelData  = @json($mapels);
const guruByKode = {};
const guruById   = {};
guruData.forEach(g => {
    guruByKode[g.kode.toUpperCase()] = g;
    guruById[g.id] = g;
});

const mapelById = {};
mapelData.forEach(m => { mapelById[m.id] = m; });

/* ─── State ─── */
let pendingChanges = {};
let currentInput   = null;

/* ─── Tooltip & Popover ─── */
const tooltip      = document.getElementById('guruTooltip');
const ttKode       = document.getElementById('ttKode');
const ttName       = document.getElementById('ttName');
const ttSub        = document.getElementById('ttSub');
const ttMapelSelect= document.getElementById('ttMapelSelect');

function showTooltip(input, guru) {
    const r = input.getBoundingClientRect();
    ttKode.textContent = guru.kode;
    ttName.textContent = guru.nama;
    ttSub.textContent  = guru.bidang_studi ? `Bidang: ${guru.bidang_studi}` : 'Umum';

    // Set mapel select
    const currentMapelId = input.dataset.mapelId;
    if (currentMapelId && mapelById[currentMapelId]) {
        ttMapelSelect.value = currentMapelId;
    } else {
        // Cari mapel yang cocok dengan bidang studi guru
        const match = guru.bidang_studi
            ? mapelData.find(m => guru.bidang_studi.toLowerCase().includes(m.nama_mapel.toLowerCase().split('/')[0].trim()))
            : null;
        ttMapelSelect.value = match ? match.id : (mapelData[0]?.id || '');
    }

    tooltip.style.top  = Math.min(r.bottom + 4, window.innerHeight - 150) + 'px';
    tooltip.style.left = Math.min(r.left, window.innerWidth - 260) + 'px';
    tooltip.style.display = 'block';
}

function hideTooltip() {
    tooltip.style.display = 'none';
}

ttMapelSelect.addEventListener('change', function() {
    if (currentInput) {
        currentInput.dataset.mapelId = this.value;
        const tag = currentInput.closest('.cell-wrapper').querySelector('.cell-mapel-tag');
        if (tag && mapelById[this.value]) {
            tag.textContent = mapelById[this.value].nama_mapel.slice(0, 10);
            tag.style.display = 'block';
        }
        validateAndQueue(currentInput);
    }
});

/* ─── Status Bar ─── */
const statusDot = document.getElementById('statusDot');
const statusMsg = document.getElementById('statusMsg');
const btnTop    = document.getElementById('btnSaveAll');
const btnBottom = document.getElementById('btnSaveAllBottom');
const countBadge= document.getElementById('changesCount');

function setStatus(msg, type = 'idle') {
    statusMsg.textContent = msg;
    const colors = {
        idle: '#94a3b8',
        warn: '#f59e0b',
        ok:   '#10b981',
        error:'#ef4444'
    };
    statusDot.style.background = colors[type] || colors.idle;
}

function updateSaveBtn() {
    const n = Object.keys(pendingChanges).length;
    [btnTop, btnBottom].forEach(b => { b.disabled = n === 0; });
    if (n > 0) {
        countBadge.style.display = 'inline';
        countBadge.textContent   = n;
        setStatus(`${n} perubahan belum disimpan`, 'warn');
    } else {
        countBadge.style.display = 'none';
        setStatus('Semua tersimpan rapi', 'ok');
    }
    recalculateJpCounts();
}

/* ─── Live Cross-Class Conflict Detection (Per Baris Sesi) ─── */
function checkRowConflicts(hari, jamId) {
    const rowInputs = document.querySelectorAll(`.grid-input[data-hari="${hari}"][data-jam-id="${jamId}"]`);
    const codeCounts = {};

    rowInputs.forEach(inp => {
        const val = inp.value.trim().toUpperCase();
        if (val) {
            codeCounts[val] = (codeCounts[val] || 0) + 1;
        }
    });

    rowInputs.forEach(inp => {
        const val = inp.value.trim().toUpperCase();
        if (!val) {
            inp.classList.remove('conflict');
            return;
        }

        // 1. Cek apakah kode guru terdaftar
        const guru = guruByKode[val];
        if (!guru) {
            inp.classList.add('conflict');
            return;
        }

        // 2. Cek apakah ada duplikasi kode guru di jam dan hari yang sama
        if (codeCounts[val] > 1) {
            inp.classList.add('conflict');
        } else {
            // Hilangkan conflict jika sebelumnya hanya bentrok duplikat
            inp.classList.remove('conflict');
        }
    });
}

/* ─── Validasi & Masuk Antrean Simpan ─── */
function validateAndQueue(input) {
    const rawVal   = input.value.trim();
    let val        = rawVal.toUpperCase();
    const original = (input.dataset.original || '').toUpperCase();
    const originalMapel = input.dataset.originalMapel || '';
    const key      = `${input.dataset.kelasId}_${input.dataset.jamId}`;
    const tag      = input.closest('.cell-wrapper').querySelector('.cell-mapel-tag');

    // Cek format KODE/MAPEL (mis. 4A/MTK)
    if (val.includes('/')) {
        const parts = val.split('/');
        val = parts[0].trim();
        const mapelCode = parts[1].trim();
        const m = mapelData.find(x => x.nama_mapel.toLowerCase().includes(mapelCode.toLowerCase()));
        if (m) input.dataset.mapelId = m.id;
    }

    input.value = val;

    // Sesi & Hari untuk conflict check
    const hari  = input.dataset.hari;
    const jamId = input.dataset.jamId;

    if (val === '') {
        tag.style.display = 'none';
        input.classList.remove('has-value', 'conflict');
        if (input.dataset.jadwalId) {
            input.classList.add('changed');
            pendingChanges[key] = {
                input,
                action: 'delete',
                jadwalId: input.dataset.jadwalId
            };
        } else {
            input.classList.remove('changed');
            delete pendingChanges[key];
        }
        checkRowConflicts(hari, jamId);
        updateSaveBtn();
        return;
    }

    const guru = guruByKode[val];
    if (!guru) {
        input.classList.add('conflict');
        input.classList.remove('has-value', 'changed');
        setStatus(`Kode "${val}" tidak ditemukan dalam data guru`, 'error');
        delete pendingChanges[key];
        checkRowConflicts(hari, jamId);
        updateSaveBtn();
        return;
    }

    // Auto-set mapel jika belum ada
    if (!input.dataset.mapelId) {
        const match = guru.bidang_studi
            ? mapelData.find(m => guru.bidang_studi.toLowerCase().includes(m.nama_mapel.toLowerCase().split('/')[0].trim()))
            : null;
        input.dataset.mapelId = match ? match.id : (mapelData[0]?.id || '');
    }

    // Update label mapel di bawah kode
    if (input.dataset.mapelId && mapelById[input.dataset.mapelId]) {
        tag.textContent = mapelById[input.dataset.mapelId].nama_mapel.slice(0, 10);
        tag.style.display = 'block';
    }

    input.dataset.guruId = guru.id;

    // Apakah sama dengan kondisi awal?
    if (val === original && input.dataset.mapelId == originalMapel) {
        delete pendingChanges[key];
        input.classList.remove('changed');
        input.classList.add('has-value');
    } else {
        input.classList.remove('has-value');
        input.classList.add('changed');
        pendingChanges[key] = {
            input,
            action:     'save',
            kelas_id:   input.dataset.kelasId,
            hari:       input.dataset.hari,
            jam_mulai:  input.dataset.jamMulai,
            jam_selesai:input.dataset.jamSelesai,
            jam_id:     input.dataset.jamId,
            guru_id:    guru.id,
            jadwal_id:  input.dataset.jadwalId || null,
            mapel_id:   input.dataset.mapelId || null,
            kode:       val
        };
    }

    checkRowConflicts(hari, jamId);
    updateSaveBtn();
}

/* ─── Navigasi Keyboard Antar Sel ─── */
function navigateCells(current, direction) {
    const visibleInputs = Array.from(document.querySelectorAll('.sesi-row:not([style*="display: none"]) .grid-input'));
    const idx = visibleInputs.indexOf(current);
    if (idx === -1) return;

    const visibleCols = document.querySelectorAll('th.col-header').length;
    let next;

    if (direction === 'right' || direction === 'Enter' || direction === 'Tab') next = visibleInputs[idx + 1];
    if (direction === 'left')  next = visibleInputs[idx - 1];
    if (direction === 'down')  next = visibleInputs[idx + visibleCols];
    if (direction === 'up')    next = visibleInputs[idx - visibleCols];

    if (next) {
        next.focus();
        next.select();
    }
}

/* ─── Event Listeners Input Grid ─── */
document.querySelectorAll('.grid-input').forEach(input => {
    input.addEventListener('focus', e => {
        currentInput = e.target;
        e.target.select();
        const val = e.target.value.trim().toUpperCase();
        if (val && guruByKode[val]) showTooltip(e.target, guruByKode[val]);
    });

    input.addEventListener('blur', e => {
        setTimeout(() => {
            if (!tooltip.contains(document.activeElement)) hideTooltip();
        }, 150);
        validateAndQueue(e.target);
    });

    input.addEventListener('input', e => {
        const val = e.target.value.trim().toUpperCase();
        if (val && guruByKode[val]) {
            showTooltip(e.target, guruByKode[val]);
        } else {
            hideTooltip();
        }
    });

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            validateAndQueue(e.target);
            navigateCells(e.target, 'Enter');
        }
        if (e.key === 'Escape') {
            e.target.value = e.target.dataset.original || '';
            e.target.blur();
            hideTooltip();
        }
        if (e.key === 'Delete') {
            e.target.value = '';
            validateAndQueue(e.target);
        }
        if (e.key === 'ArrowRight' && e.target.selectionStart === e.target.value.length) {
            e.preventDefault(); navigateCells(e.target, 'right');
        }
        if (e.key === 'ArrowLeft' && e.target.selectionStart === 0) {
            e.preventDefault(); navigateCells(e.target, 'left');
        }
        if (e.key === 'ArrowDown')  { e.preventDefault(); navigateCells(e.target, 'down'); }
        if (e.key === 'ArrowUp')    { e.preventDefault(); navigateCells(e.target, 'up'); }
        if (e.key === 'Tab') {
            e.preventDefault();
            navigateCells(e.target, e.shiftKey ? 'left' : 'right');
        }
    });
});

/* ─── Save All Ajax ─── */
async function saveAll() {
    const changes = Object.values(pendingChanges);
    if (!changes.length) return;

    // Cek apakah ada konflik yang masih aktif
    const hasConflicts = document.querySelector('.grid-input.conflict');
    if (hasConflicts) {
        if (!confirm('Masih terdapat sel yang bentrok/konflik warna merah. Lanjutkan menyimpan bagian yang valid?')) {
            return;
        }
    }

    setStatus('Menyimpan perubahan…', 'idle');
    [btnTop, btnBottom].forEach(b => {
        b.disabled = true;
        b.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan…';
    });

    const payload = changes.map(c => {
        if (c.action === 'delete') {
            return { action: 'delete', jadwal_id: c.jadwalId };
        }
        return {
            action:      'save',
            kelas_id:    c.kelas_id,
            guru_id:     c.guru_id,
            mapel_id:    c.mapel_id,
            hari:        c.hari,
            jam_mulai:   c.jam_mulai,
            jam_selesai: c.jam_selesai,
            jam_id:      c.jam_id,
            jadwal_id:   c.jadwal_id
        };
    });

    try {
        const res = await fetch('{{ route("jadwal.grid-store") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body:    JSON.stringify({ changes: payload }),
        });
        const data = await res.json();

        if (data.success) {
            let errorCount = 0;
            data.results.forEach((r, i) => {
                const c = changes[i];
                if (r.success) {
                    if (c.action === 'delete') {
                        c.input.dataset.jadwalId = '';
                        c.input.dataset.guruId   = '';
                        c.input.dataset.mapelId  = '';
                        c.input.dataset.original = '';
                        c.input.dataset.originalMapel = '';
                        c.input.classList.remove('has-value', 'changed', 'conflict');
                    } else {
                        c.input.dataset.jadwalId = r.id;
                        c.input.dataset.original = c.kode;
                        c.input.dataset.originalMapel = c.mapel_id;
                        c.input.classList.remove('changed');
                        c.input.classList.add('has-value');
                    }
                } else {
                    c.input.classList.add('conflict');
                    c.input.classList.remove('changed');
                    errorCount++;
                }
            });

            pendingChanges = {};
            updateSaveBtn();
            setStatus(
                errorCount
                    ? `Selesai dengan ${errorCount} bentrok server – periksa sel merah`
                    : `Berhasil! Semua ${changes.length} jadwal tersimpan`,
                errorCount ? 'error' : 'ok'
            );
        } else {
            setStatus('Gagal menyimpan: ' + (data.message || 'Kesalahan server'), 'error');
        }
    } catch (err) {
        setStatus('Kesalahan jaringan: ' + err.message, 'error');
    } finally {
        [btnTop, btnBottom].forEach(b => {
            b.disabled = Object.keys(pendingChanges).length === 0;
        });
        btnTop.innerHTML = '<i class="fas fa-cloud-arrow-up me-1"></i> <span>Simpan Semua</span>' +
            `<span class="badge bg-warning text-dark ms-1" id="changesCount" style="${Object.keys(pendingChanges).length ? '' : 'display:none'}">${Object.keys(pendingChanges).length}</span>`;
        btnBottom.innerHTML = '<i class="fas fa-cloud-arrow-up me-1"></i> Simpan Semua';
    }
}

btnTop.addEventListener('click', saveAll);
btnBottom.addEventListener('click', saveAll);

/* ─── Filter Hari ─── */
document.querySelectorAll('.filter-hari-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-hari-btn').forEach(b => {
            b.classList.remove('btn-primary');
            b.classList.add('btn-light');
        });
        this.classList.remove('btn-light');
        this.classList.add('btn-primary');

        const hari = this.dataset.hari;
        const dayRows  = document.querySelectorAll('.day-row');
        const sesiRows = document.querySelectorAll('.sesi-row');

        if (hari === 'all') {
            dayRows.forEach(r => r.style.display = '');
            sesiRows.forEach(r => r.style.display = '');
        } else {
            dayRows.forEach(r => {
                r.style.display = (r.dataset.dayGroup === hari) ? '' : 'none';
            });
            sesiRows.forEach(r => {
                r.style.display = (r.dataset.day === hari) ? '' : 'none';
            });
        }
    });
});

/* ─── Hitung Beban JP Live ─── */
function recalculateJpCounts() {
    const counts = {};
    let totalJp = 0;

    document.querySelectorAll('.grid-input').forEach(input => {
        const val = input.value.trim().toUpperCase();
        if (val && guruByKode[val]) {
            const g = guruByKode[val];
            counts[g.id] = (counts[g.id] || 0) + 1;
            totalJp++;
        }
    });

    document.querySelectorAll('.jp-counter').forEach(badge => {
        const id = badge.id.replace('jpCount_', '');
        badge.textContent = counts[id] || 0;
    });

    const totalEl = document.getElementById('totalJpCount');
    if (totalEl) totalEl.textContent = totalJp;
}

/* ─── Sorot Jadwal Guru ─── */
function highlightGuru(kode) {
    const k = kode.toUpperCase();
    document.querySelectorAll('.grid-input').forEach(input => {
        if (input.value.trim().toUpperCase() === k) {
            input.classList.add('highlighted');
        } else {
            input.classList.remove('highlighted');
        }
    });
    bootstrap.Modal.getInstance(document.getElementById('guruListModal'))?.hide();
    setStatus(`Menyorot seluruh jadwal guru [${k}]`, 'idle');
}

document.getElementById('btnResetHighlight')?.addEventListener('click', () => {
    document.querySelectorAll('.grid-input.highlighted').forEach(el => el.classList.remove('highlighted'));
    setStatus('Sorotan dibersihkan', 'idle');
});

/* ─── Sisipkan Kode dari Modal ke Sel Aktif ─── */
function insertKode(kode) {
    if (!currentInput) {
        alert('Pilih sel jadwal terlebih dahulu di grid, lalu buka modal ini.');
        return;
    }
    currentInput.value = kode;
    validateAndQueue(currentInput);
    bootstrap.Modal.getInstance(document.getElementById('guruListModal'))?.hide();
    currentInput.focus();
}

/* ─── Filter Pencarian Guru di Modal ─── */
document.getElementById('searchGuru')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.guru-modal-row').forEach(row => {
        const txt = (row.dataset.kode + ' ' + row.dataset.nama + ' ' + row.dataset.bidang).toLowerCase();
        row.style.display = txt.includes(q) ? '' : 'none';
    });
});

// Hitung awal
recalculateJpCounts();
// Jalankan initial cross-check
@foreach($jamPelajaran as $jam)
    checkRowConflicts('{{ $jam->hari }}', {{ $jam->id }});
@endforeach
</script>
@endsection