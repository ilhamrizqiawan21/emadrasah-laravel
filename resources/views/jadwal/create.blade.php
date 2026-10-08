@extends('layouts.app')

@section('title', 'Tambah Jadwal Pelajaran')

@section('content')
<x-page-header title="Tambah Jadwal Pelajaran">
    Tambahkan jadwal pelajaran manual per sesi.
    <x-slot:actions>
        <a href="{{ route('jadwal.grid') }}" class="btn btn-outline-success me-2">
            <i class="fas fa-table-cells me-1"></i> Mode Grid
        </a>
        <a href="{{ route('jadwal.index') }}" class="btn btn-light border">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </x-slot:actions>
</x-page-header>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <span class="fw-bold fs-6 text-dark">
                    <i class="fas fa-calendar-plus text-primary me-2"></i>Form Input Jadwal
                </span>
                <span class="badge bg-light text-muted border">Input Manual</span>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('jadwal.store') }}" id="formJadwal">
                    @csrf

                    {{-- Hidden jam values for backend compatibility --}}
                    <input type="hidden" name="jam_mulai" id="jam_mulai" value="{{ old('jam_mulai') }}">
                    <input type="hidden" name="jam_selesai" id="jam_selesai" value="{{ old('jam_selesai') }}">

                    <div class="row g-4">
                        {{-- Bagian 1: Waktu & Kelas --}}
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3 letter-spacing-1">
                                <i class="fas fa-clock text-primary me-1"></i> 1. Waktu &amp; Kelas
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                                    <select name="kelas_id" id="kelas_select" class="form-select @error('kelas_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Kelas --</option>
                                        @foreach($kelas as $k)
                                            <option value="{{ $k->id }}" {{ old('kelas_id', request('kelas_id')) == $k->id ? 'selected' : '' }}>
                                                Kelas {{ $k->nama_kelas }} (Tingkat {{ $k->tingkat }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('kelas_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Hari <span class="text-danger">*</span></label>
                                    <select name="hari" id="hari_select" class="form-select @error('hari') is-invalid @enderror" required>
                                        <option value="">-- Pilih Hari --</option>
                                        @foreach(['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'] as $h)
                                            <option value="{{ $h }}" {{ old('hari', request('hari')) == $h ? 'selected' : '' }}>{{ $h }}</option>
                                        @endforeach
                                    </select>
                                    @error('hari')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Sesi Jam Pelajaran <span class="text-danger">*</span></label>
                                    <select name="sesi_id" id="sesi_select" class="form-select @error('sesi_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Hari Dahulu --</option>
                                    </select>
                                    @error('sesi_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-12" id="slotStatusBox" style="display:none;">
                                    <div class="p-3 rounded-3" id="slotStatusAlert">
                                        <i class="fas fa-info-circle me-1" id="slotStatusIcon"></i>
                                        <span id="slotStatusMsg"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-2 border-light">

                        {{-- Bagian 2: Pengajar & Mapel --}}
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted fw-bold small mb-3 letter-spacing-1">
                                <i class="fas fa-chalkboard-user text-primary me-1"></i> 2. Pengajar &amp; Mata Pelajaran
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-5">
                                    <label class="form-label fw-semibold">Guru Pengajar <span class="text-danger">*</span></label>
                                    <select name="guru_id" id="guru_select" class="form-select @error('guru_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Guru --</option>
                                        @foreach($gurus as $g)
                                            <option value="{{ $g->id }}"
                                                    data-kode="{{ $g->kode }}"
                                                    data-bidang="{{ $g->bidang_studi }}"
                                                    {{ old('guru_id') == $g->id ? 'selected' : '' }}>
                                                [{{ $g->kode }}] {{ $g->nama }} {{ $g->bidang_studi ? '— ' . $g->bidang_studi : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('guru_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label fw-semibold">Mata Pelajaran <span class="text-danger">*</span></label>
                                    <select name="mapel_id" id="mapel_select" class="form-select @error('mapel_id') is-invalid @enderror" required>
                                        <option value="">-- Pilih Mapel --</option>
                                        @foreach($mapels as $m)
                                            <option value="{{ $m->id }}" {{ old('mapel_id') == $m->id ? 'selected' : '' }}>
                                                {{ $m->nama_mapel }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('mapel_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Ruangan <small class="text-muted fw-normal">(Opsional)</small></label>
                                    <input type="text" name="ruang" class="form-control" value="{{ old('ruang') }}" placeholder="Contoh: Lab IPA, Lapangan">
                                </div>

                                <div class="col-12" id="conflictBox" style="display:none;">
                                    <div class="p-3 rounded-3 bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">
                                        <i class="fas fa-triangle-exclamation me-1"></i>
                                        <strong>Peringatan Konflik:</strong> <span id="conflictMsg"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                        <span class="text-muted small">
                            <i class="fas fa-lightbulb text-warning me-1"></i>
                            Jadwal juga dapat diatur lebih cepat di <a href="{{ route('jadwal.grid') }}" class="fw-semibold text-primary">Mode Grid</a>
                        </span>
                        <div class="d-flex gap-2">
                            <a href="{{ route('jadwal.index') }}" class="btn btn-light border px-4">Batal</a>
                            <button type="submit" class="btn btn-primary px-4" id="btnSubmit">
                                <i class="fas fa-save me-1"></i> Simpan Jadwal
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    const jamPelajaranData = @json($jamPelajaran);
    const existingJadwals  = @json($existingJadwals ?? []);
    const guruData         = @json($gurus);
    const mapelData        = @json($mapels);
    const kelasData        = @json($kelas);

    const hariSelect   = document.getElementById('hari_select');
    const sesiSelect   = document.getElementById('sesi_select');
    const kelasSelect  = document.getElementById('kelas_select');
    const guruSelect   = document.getElementById('guru_select');
    const mapelSelect  = document.getElementById('mapel_select');
    const jamMulai     = document.getElementById('jam_mulai');
    const jamSelesai   = document.getElementById('jam_selesai');
    const slotBox      = document.getElementById('slotStatusBox');
    const slotAlert    = document.getElementById('slotStatusAlert');
    const slotMsg      = document.getElementById('slotStatusMsg');
    const conflictBox  = document.getElementById('conflictBox');
    const conflictMsg  = document.getElementById('conflictMsg');
    const btnSubmit    = document.getElementById('btnSubmit');

    function populateSesi() {
        const selectedHari = hariSelect.value;
        sesiSelect.innerHTML = '<option value="">-- Pilih Sesi --</option>';
        jamMulai.value = '';
        jamSelesai.value = '';
        slotBox.style.display = 'none';

        if (!selectedHari) return;

        const filtered = jamPelajaranData.filter(jp => jp.hari === selectedHari);
        filtered.forEach(jp => {
            const opt = document.createElement('option');
            opt.value = jp.id;
            const m = jp.jam_mulai.slice(0,5);
            const s = jp.jam_selesai.slice(0,5);
            opt.textContent = `Sesi ${jp.sesi_ke} (${m} – ${s})`;
            opt.dataset.mulai = jp.jam_mulai;
            opt.dataset.selesai = jp.jam_selesai;
            opt.dataset.sesi = jp.sesi_ke;
            sesiSelect.appendChild(opt);
        });
    }

    function checkSlotAndConflict() {
        const hari    = hariSelect.value;
        const sesiId  = sesiSelect.value;
        const kelasId = parseInt(kelasSelect.value);
        const guruId  = parseInt(guruSelect.value);

        slotBox.style.display = 'none';
        conflictBox.style.display = 'none';
        btnSubmit.disabled = false;

        const opt = sesiSelect.options[sesiSelect.selectedIndex];
        if (opt && opt.dataset.mulai) {
            jamMulai.value = opt.dataset.mulai;
            jamSelesai.value = opt.dataset.selesai;
        }

        if (!hari || !sesiId) return;

        const sesiMulai   = jamMulai.value;
        const sesiSelesai = jamSelesai.value;

        // 1. Cek apakah slot di kelas ini sudah ada yang mengisi
        if (kelasId) {
            const taken = existingJadwals.find(j =>
                j.kelas_id === kelasId &&
                j.hari === hari &&
                (j.jam_pelajaran_id == sesiId || (j.jam_mulai < sesiSelesai && j.jam_selesai > sesiMulai))
            );
            if (taken) {
                const g = guruData.find(x => x.id === taken.guru_id);
                slotAlert.className = 'p-3 rounded-3 bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25';
                slotMsg.innerHTML = `<strong>Catatan:</strong> Slot ini saat ini sudah diisi oleh guru <strong>${g ? g.nama : 'Lain'}</strong>. Menyimpan akan menimpa jadwal tersebut.`;
                slotBox.style.display = 'block';
            }
        }

        // 2. Cek apakah guru yang dipilih bentrok di kelas lain pada waktu yang sama
        if (guruId) {
            const clash = existingJadwals.find(j =>
                j.guru_id === guruId &&
                j.hari === hari &&
                j.kelas_id !== kelasId &&
                (j.jam_mulai < sesiSelesai && j.jam_selesai > sesiMulai)
            );
            if (clash) {
                const k = kelasData.find(x => x.id === clash.kelas_id);
                const g = guruData.find(x => x.id === guruId);
                conflictMsg.innerHTML = `Guru <strong>${g ? g.nama : ''}</strong> sudah memiliki jadwal mengajar di <strong>Kelas ${k ? k.nama_kelas : clash.kelas_id}</strong> pada waktu ini (${sesiMulai.slice(0,5)} – ${sesiSelesai.slice(0,5)}).`;
                conflictBox.style.display = 'block';
                btnSubmit.disabled = true;
            }
        }
    }

    // Auto-select mapel jika guru memiliki bidang studi yang cocok
    guruSelect.addEventListener('change', function() {
        const opt = guruSelect.options[guruSelect.selectedIndex];
        const bidang = opt ? opt.dataset.bidang : '';
        if (bidang && !mapelSelect.value) {
            const match = mapelData.find(m => bidang.toLowerCase().includes(m.nama_mapel.toLowerCase().split('/')[0].trim()));
            if (match) mapelSelect.value = match.id;
        }
        checkSlotAndConflict();
    });

    hariSelect.addEventListener('change', () => {
        populateSesi();
        checkSlotAndConflict();
    });
    sesiSelect.addEventListener('change', checkSlotAndConflict);
    kelasSelect.addEventListener('change', checkSlotAndConflict);

    // Initial populate jika ada old value
    if (hariSelect.value) {
        populateSesi();
        const oldSesi = '{{ old("sesi_id") }}';
        if (oldSesi) {
            sesiSelect.value = oldSesi;
            checkSlotAndConflict();
        }
    }
</script>
@endsection