@extends('layouts.app')

@section('title', 'Input Jadwal – Grid')

@section('content')

<div class="page-header">
    <div>
        <div class="page-title">
            <i class="fas fa-table-cells me-2" style="color:var(--em-primary)"></i>Input Jadwal
        </div>
        <div class="page-subtitle">
            Ketik kode guru pada sel · Enter untuk pindah · Esc untuk batal · <kbd style="font-size:0.72rem;padding:1px 5px;border:1px solid #d1d5db;border-radius:4px;">Del</kbd> untuk hapus
        </div>
    </div>
    <div class="page-actions">
        <a href="{{ route('jadwal.index') }}" class="btn-kode">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
        <button type="button" class="btn-kode" data-bs-toggle="modal" data-bs-target="#guruListModal" aria-label="Buka daftar kode guru">
            <i class="fas fa-id-card-alt" style="color:var(--g-primary)"></i> Daftar Kode
        </button>
        <button class="btn-save" id="btnSaveAll" disabled aria-label="Simpan semua perubahan jadwal">
            <i class="fas fa-cloud-arrow-up"></i>
            <span>Simpan Semua</span>
            <span class="changes-pill" id="changesCount" style="display:none">0</span>
        </button>
    </div>
</div>

<div class="legend">
    <span class="legend-item"><span class="legend-dot filled"></span> Terisi</span>
    <span class="legend-item"><span class="legend-dot changed"></span> Berubah (belum disimpan)</span>
    <span class="legend-item"><span class="legend-dot conflict"></span> Konflik / Kode tidak valid</span>
    <span class="legend-item"><span class="legend-dot empty"></span> Kosong</span>
</div>

<div class="grid-scroll-shell">
    <table class="grid-table">
        <thead>
            <tr>
                <th class="col-header-jam">Sesi &amp; Waktu</th>
                @foreach($kelas as $k)
                    <th class="col-header">{{ $k->nama_kelas }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                $currentHari = null;
            @endphp

            @foreach($jamPelajaran as $jam)
                {{-- Baris pemisah hari (hanya jika hari berubah) --}}
                @if($jam->hari !== $currentHari)
                    @php $currentHari = $jam->hari; @endphp
                    <tr class="day-row">
                        <td colspan="{{ count($kelas) + 1 }}">
                            <span class="day-label">{{ $jam->hari }}</span>
                         </td>
                     </tr>
                @endif

                {{-- Baris jadwal per sesi --}}
                <tr>
                    <td class="sesi-cell">
                        <span class="sesi-no">Sesi {{ $jam->sesi_ke }}</span>
                        <span class="sesi-time">{{ substr($jam->jam_mulai,0,5) }} – {{ substr($jam->jam_selesai,0,5) }}</span>
                    </td>

                    @foreach($kelas as $k)
                        @php
                            $key = $k->id . '_' . $jam->hari . '_' . $jam->id;
                            $existing = $jadwalGrid[$key] ?? null;
                            $kodeVal = $existing ? $existing['guru_kode'] : '';
                        @endphp
                        <td class="input-cell">
                            <input type="text"
                                   class="grid-input {{ $kodeVal ? 'has-value' : '' }}"
                                   data-kelas-id="{{ $k->id }}"
                                   data-jam-id="{{ $jam->id }}"
                                   data-hari="{{ $jam->hari }}"
                                   data-jam-mulai="{{ $jam->jam_mulai }}"
                                   data-jam-selesai="{{ $jam->jam_selesai }}"
                                   data-jadwal-id="{{ $existing['jadwal_id'] ?? '' }}"
                                   data-guru-id="{{ $existing['guru_id'] ?? '' }}"
                                   data-mapel-id="{{ $existing['mapel_id'] ?? '' }}"
                                   data-original="{{ $kodeVal }}"
                                   value="{{ $kodeVal }}"
                                   placeholder="—"
                                   aria-label="Kode guru kelas {{ $k->nama_kelas }} {{ $jam->hari }} sesi {{ $jam->sesi_ke }}"
                                   autocomplete="off">
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="status-bar">
    <div class="status-text">
        <div class="status-dot" id="statusDot"></div>
        <span id="statusMsg">Siap — klik sel untuk mulai mengisi</span>
    </div>
    <button class="btn-save" id="btnSaveAllBottom" disabled aria-label="Simpan semua perubahan jadwal">
        <i class="fas fa-cloud-arrow-up"></i> Simpan Semua
    </button>
</div>

<div class="guru-tooltip" id="guruTooltip">
    <div class="tt-name" id="ttName"></div>
    <div class="tt-sub" id="ttSub"></div>
</div>

<div class="modal fade" id="guruListModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content" style="border-radius:var(--radius-lg);overflow:hidden;border:none;">
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title fw-bold">
                    <i class="fas fa-id-card-alt me-2"></i>Daftar Kode Guru
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Tutup daftar kode guru"></button>
            </div>
            <div class="modal-body p-3">
                <div class="input-group mb-3">
                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" id="searchGuru" class="form-control" placeholder="Cari kode, nama, atau bidang studi…">
                </div>
                <div class="table-responsive" style="max-height:420px;">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th style="width:80px">Kode</th>
                                <th>Nama Guru</th>
                                <th>Bidang Studi</th>
                                <th style="width:60px"></th>
                            </tr>
                        </thead>
                        <tbody id="guruListBody">
                            @foreach($gurus as $g)
                            <tr class="guru-row"
                                data-kode="{{ strtolower($g->kode) }}"
                                data-nama="{{ strtolower($g->nama) }}"
                                data-bidang="{{ strtolower($g->bidang_studi ?? '') }}">
                                <td><span class="kode-badge">{{ $g->kode }}</span></td>
                                <td class="fw-semibold">{{ $g->nama }}</td>
                                <td class="text-muted small">{{ $g->bidang_studi ?? '–' }}</td>
                                <td>
                                    <button class="btn btn-xs btn-outline-success py-0 px-2"
                                            onclick="insertKode('{{ $g->kode }}')"
                                            title="Sisipkan kode ini">
                                        <i class="fas fa-arrow-left"></i>
                                    </button>
                                 </td>
                             </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-2">
                <small class="text-muted">Klik baris atau tombol <i class="fas fa-arrow-left"></i> untuk menyisipkan ke sel aktif</small>
            </div>
        </div>
    </div>
</div>

<script>
/* ─── Data master ─────────────────────────────────── */
const guruData   = @json($gurus);
const mapelData  = @json($mapels);
const guruByKode = {};
guruData.forEach(g => { guruByKode[g.kode.toUpperCase()] = g; });

/* ─── State ────────────────────────────────────────── */
let pendingChanges = {};
let currentInput   = null;

/* ─── Tooltip helpers ─────────────────────────────── */
const tooltip = document.getElementById('guruTooltip');
const ttName  = document.getElementById('ttName');
const ttSub   = document.getElementById('ttSub');

function showTooltip(input, guru) {
    const r = input.getBoundingClientRect();
    ttName.textContent = guru.nama;
    ttSub.textContent  = guru.bidang_studi ? `Bidang: ${guru.bidang_studi}` : '';
    tooltip.style.top  = (r.bottom + window.scrollY + 6) + 'px';
    tooltip.style.left = Math.min(r.left + window.scrollX, window.innerWidth - 220) + 'px';
    tooltip.classList.add('visible');
}
function hideTooltip() { tooltip.classList.remove('visible'); }

/* ─── Status bar ──────────────────────────────────── */
const statusDot = document.getElementById('statusDot');
const statusMsg = document.getElementById('statusMsg');

function setStatus(msg, type = 'idle') {
    statusMsg.textContent = msg;
    statusDot.className   = 'status-dot' + (type === 'warn' ? ' active' : type === 'ok' ? ' saved' : type === 'error' ? ' error' : '');
}

/* ─── Save button state ───────────────────────────── */
const btnTop    = document.getElementById('btnSaveAll');
const btnBottom = document.getElementById('btnSaveAllBottom');
const countBadge= document.getElementById('changesCount');

function updateSaveBtn() {
    const n = Object.keys(pendingChanges).length;
    [btnTop, btnBottom].forEach(b => { b.disabled = n === 0; });
    if (n > 0) {
        countBadge.style.display = 'inline';
        countBadge.textContent   = n;
        setStatus(`${n} perubahan belum disimpan`, 'warn');
    } else {
        countBadge.style.display = 'none';
        setStatus('Semua tersimpan', 'ok');
    }
}

/* ─── Validate & queue ────────────────────────────── */
function validateAndQueue(input) {
    hideTooltip();
    const val      = input.value.trim().toUpperCase();
    const original = (input.dataset.original || '').toUpperCase();
    input.value    = val;
    const key      = `${input.dataset.kelasId}_${input.dataset.jamId}`;

    if (val === original) {
        delete pendingChanges[key];
        if (val) {
            input.classList.remove('changed', 'conflict');
            input.classList.add('has-value');
        } else {
            input.classList.remove('changed', 'conflict', 'has-value');
        }
        updateSaveBtn();
        return;
    }

    if (val === '') {
        input.classList.remove('has-value', 'conflict', 'changed');
        if (input.dataset.jadwalId) {
            pendingChanges[key] = { input, action: 'delete', jadwalId: input.dataset.jadwalId };
        } else {
            delete pendingChanges[key];
        }
        updateSaveBtn();
        return;
    }

    const guru = guruByKode[val];
    if (!guru) {
        input.classList.add('conflict');
        input.classList.remove('has-value', 'changed');
        setStatus(`Kode "${val}" tidak ditemukan dalam data guru`, 'error');
        delete pendingChanges[key];
        updateSaveBtn();
        return;
    }

    input.classList.remove('conflict', 'has-value');
    input.classList.add('changed');
    pendingChanges[key] = {
        input,
        action:     'save',
        kelasId:    input.dataset.kelasId,
        hari:       input.dataset.hari,
        jamMulai:   input.dataset.jamMulai,
        jamSelesai: input.dataset.jamSelesai,
        guruId:     guru.id,
        jadwalId:   input.dataset.jadwalId || null,
        mapelId:    input.dataset.mapelId  || null,
        kode:       val,
    };
    updateSaveBtn();
}

/* ─── Arrow-key navigation helper ────────────────── */
function navigateCells(current, direction) {
    const inputs = Array.from(document.querySelectorAll('.grid-input'));
    const idx    = inputs.indexOf(current);
    const cols   = document.querySelectorAll('.col-header').length;
    let   next;
    if (direction === 'right' || direction === 'Enter') next = inputs[idx + 1];
    if (direction === 'left')                           next = inputs[idx - 1];
    if (direction === 'down')                           next = inputs[idx + cols];
    if (direction === 'up')                             next = inputs[idx - cols];
    if (next) { next.focus(); next.select(); }
}

/* ─── Attach listeners ────────────────────────────── */
document.querySelectorAll('.grid-input').forEach(input => {
    input.addEventListener('focus', e => {
        currentInput = e.target;
        e.target.select();
        const val = e.target.value.trim().toUpperCase();
        if (val && guruByKode[val]) showTooltip(e.target, guruByKode[val]);
    });

    input.addEventListener('blur', e => {
        validateAndQueue(e.target);
    });

    input.addEventListener('input', e => {
        const val  = e.target.value.trim().toUpperCase();
        e.target.classList.remove('conflict');
        if (val && guruByKode[val]) {
            showTooltip(e.target, guruByKode[val]);
        } else if (val) {
            ttName.textContent = `"${val}" – tidak dikenal`;
            ttSub.textContent  = 'Periksa kode pada daftar guru';
            const r = e.target.getBoundingClientRect();
            tooltip.style.top  = (r.bottom + window.scrollY + 6) + 'px';
            tooltip.style.left = Math.min(r.left + window.scrollX, window.innerWidth - 220) + 'px';
            tooltip.classList.add('visible');
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
        }
        if (e.key === 'Delete' || e.key === 'Backspace') {
            if (!e.target.value) {
                e.target.value = '';
                validateAndQueue(e.target);
            }
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

    input.addEventListener('mouseenter', () => {
        const val = input.value.trim().toUpperCase();
        if (val && guruByKode[val]) showTooltip(input, guruByKode[val]);
    });
    input.addEventListener('mouseleave', hideTooltip);
});

/* ─── Save all ────────────────────────────────────── */
async function saveAll() {
    const changes = Object.values(pendingChanges);
    if (!changes.length) return;

    setStatus('Menyimpan…', 'idle');
    [btnTop, btnBottom].forEach(b => {
        b.disabled = true;
        b.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan…';
    });

    const payload = changes.map(c => {
        if (c.action === 'delete') return { action: 'delete', jadwal_id: c.jadwalId };

        let mapelId = c.input.dataset.mapelId;
        if (!mapelId) {
            const guru  = guruByKode[c.kode];
            const found = guru?.bidang_studi
                ? mapelData.find(m => guru.bidang_studi.toLowerCase().includes(m.nama_mapel.toLowerCase().split('/')[0].trim()))
                : null;
            mapelId = found ? found.id : (mapelData[0]?.id ?? null);
        }
        return {
            action:      'save',
            kelas_id:    c.kelasId,
            guru_id:     c.guruId,
            mapel_id:    mapelId,
            hari:        c.hari,
            jam_mulai:   c.jamMulai,
            jam_selesai: c.jamSelesai,
            jadwal_id:   c.jadwalId,
        };
    });

    try {
        const res  = await fetch('{{ route("jadwal.grid-store") }}', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            body:    JSON.stringify({ changes: payload }),
        });
        const data = await res.json();

        if (data.success) {
            let errors = 0;
            data.results.forEach((r, i) => {
                const c = changes[i];
                if (r.success) {
                    if (c.action === 'delete') {
                        c.input.dataset.jadwalId = '';
                        c.input.dataset.guruId   = '';
                        c.input.dataset.mapelId  = '';
                        c.input.dataset.original = '';
                        c.input.classList.remove('has-value', 'changed');
                    } else {
                        c.input.dataset.jadwalId = r.id;
                        c.input.dataset.original = c.kode;
                        c.input.classList.remove('changed');
                        c.input.classList.add('has-value');
                    }
                } else {
                    c.input.classList.add('conflict');
                    c.input.classList.remove('changed');
                    errors++;
                }
            });
            pendingChanges = {};
            updateSaveBtn();
            setStatus(
                errors
                    ? `Disimpan dengan ${errors} konflik – periksa sel merah`
                    : `Semua ${changes.length} perubahan berhasil disimpan`,
                errors ? 'error' : 'ok'
            );
        } else {
            setStatus('Gagal menyimpan: ' + (data.message || 'Server error'), 'error');
        }
    } catch (err) {
        setStatus('Error jaringan: ' + err.message, 'error');
    } finally {
        [btnTop, btnBottom].forEach(b => {
            b.disabled = Object.keys(pendingChanges).length === 0;
            b.innerHTML = '<i class="fas fa-cloud-arrow-up me-1"></i> Simpan Semua';
        });
        btnTop.innerHTML = '<i class="fas fa-cloud-arrow-up"></i> <span>Simpan Semua</span>' +
            `<span class="changes-pill" id="changesCount" style="${Object.keys(pendingChanges).length ? '' : 'display:none'}">${Object.keys(pendingChanges).length}</span>`;
    }
}

btnTop.addEventListener('click', saveAll);
btnBottom.addEventListener('click', saveAll);

/* ─── Modal search ────────────────────────────────── */
document.getElementById('searchGuru')?.addEventListener('input', function () {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#guruListBody tr').forEach(row => {
        const match = (row.dataset.kode + row.dataset.nama + row.dataset.bidang).includes(q);
        row.style.display = match ? '' : 'none';
    });
});

/* ─── Insert kode from modal ──────────────────────── */
function insertKode(kode) {
    if (!currentInput) {
        setStatus('Pilih sel terlebih dahulu, lalu buka daftar kode', 'error');
        return;
    }
    currentInput.value = kode;
    currentInput.dispatchEvent(new Event('input'));
    validateAndQueue(currentInput);
    bootstrap.Modal.getInstance(document.getElementById('guruListModal'))?.hide();
    currentInput.focus();
}

/* ─── Click on guru row in modal ─────────────────── */
document.querySelectorAll('.guru-row').forEach(row => {
    row.addEventListener('click', function (e) {
        if (e.target.closest('button')) return;
        const kode = row.querySelector('.kode-badge')?.textContent?.trim();
        if (kode) insertKode(kode);
    });
});
</script>
@endsection
