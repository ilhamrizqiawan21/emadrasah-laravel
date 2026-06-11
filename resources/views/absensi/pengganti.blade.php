@extends('layouts.app')

@section('title', 'Tunjuk Guru Infaler')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <a href="{{ route('absensi.index', ['tanggal' => $agenda->tanggal->format('Y-m-d')]) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Absensi
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="mb-0 fw-semibold">
                        <i class="fas fa-user-friends me-2 text-primary"></i> Tunjuk Guru Pengganti
                    </h4>
                    <p class="text-muted mb-0 mt-1">
                        Guru: <strong>{{ $agenda->guru->nama }}</strong> ({{ $agenda->guru->kode }})<br>
                        Tanggal: <strong>{{ $agenda->tanggal->format('d F Y') }}</strong>
                    </p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('absensi.store-pengganti', $agenda->id) }}" id="formPengganti">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sesi Jam Pelajaran <span class="text-danger">*</span></label>
                                <select name="jam_pelajaran_id" id="jam_pelajaran_id" class="form-select" required>
                                    <option value="">-- Pilih Sesi --</option>
                                    @foreach($jamList as $jam)
                                        <option value="{{ $jam->id }}" data-jam-mulai="{{ $jam->jam_mulai }}" data-jam-selesai="{{ $jam->jam_selesai }}">
                                            {{ $jam->hari }} - Sesi {{ $jam->sesi_ke }} 
                                            ({{ substr($jam->jam_mulai,0,5) }} – {{ substr($jam->jam_selesai,0,5) }})
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Pilih jam yang akan digantikan</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Guru Pengganti <span class="text-danger">*</span></label>
                                <select name="guru_pengganti_id" id="guru_pengganti_id" class="form-select" required>
                                    <option value="">-- Pilih Guru --</option>
                                    @foreach($guruPenggantiOptions as $g)
                                        <option value="{{ $g->id }}" data-nama="{{ $g->nama }}">
                                            {{ $g->kode }} - {{ $g->nama }} 
                                            @if($g->bidang_studi) ({{ $g->bidang_studi }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text">Sistem akan cek konflik jadwal otomatis</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label">Keterangan (opsional)</label>
                                <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan tentang penggantian..."></textarea>
                            </div>
                            <div class="col-12 text-end">
                                <button type="submit" class="btn btn-primary px-4" id="btnTugaskan">
                                    <i class="fas fa-check-circle me-1"></i> Tugaskan infaler
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info card -->
            <div class="card bg-light mt-4 border-0">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Informasi Konflik Jadwal</h6>
                            <p class="mb-0 small text-muted">
                                Sistem akan memeriksa apakah guru pengganti sudah memiliki jadwal mengajar di hari dan jam yang sama.
                                Jika terjadi konflik, Anda akan diminta memilih guru lain.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Data jadwal guru dari controller
    const jadwalGuru = @json($jadwalGuru);

    // Elemen
    const jamSelect = document.getElementById('jam_pelajaran_id');
    const guruSelect = document.getElementById('guru_pengganti_id');
    const originalOptions = Array.from(guruSelect.options);

    // Fungsi untuk filter guru berdasarkan jam yang dipilih
    function filterGuru() {
        const selectedJamOption = jamSelect.options[jamSelect.selectedIndex];
        if (!selectedJamOption.value) {
            // Tampilkan semua guru
            guruSelect.innerHTML = '';
            originalOptions.forEach(opt => {
                if (opt.value !== '') guruSelect.appendChild(opt.cloneNode(true));
            });
            return;
        }

        const jamMulai = selectedJamOption.dataset.jamMulai;
        const jamSelesai = selectedJamOption.dataset.jamSelesai;

        // Filter guru: hanya guru yang tidak memiliki jadwal di jam tersebut
        const filteredOptions = originalOptions.filter(opt => {
            if (opt.value === '') return false; // skip placeholder
            const guruId = parseInt(opt.value);
            const jadwal = jadwalGuru[guruId] || [];
            const conflict = jadwal.some(j => {
                // Cek apakah ada jadwal yang overlapping
                return (j.jam_mulai <= jamSelesai && j.jam_selesai >= jamMulai);
            });
            return !conflict; // hanya guru yang tidak konflik
        });

        // Tampilkan hasil filter
        guruSelect.innerHTML = '<option value="">-- Pilih Guru --</option>';
        filteredOptions.forEach(opt => {
            guruSelect.appendChild(opt.cloneNode(true));
        });

        // Jika hanya satu guru, bisa auto-select? Tidak, biar user pilih.
        if (filteredOptions.length === 0) {
            const info = document.createElement('option');
            info.text = 'Tidak ada guru tersedia untuk sesi ini';
            info.disabled = true;
            guruSelect.appendChild(info);
        }
    }

    // Event listener saat sesi berubah
    jamSelect.addEventListener('change', filterGuru);

    // Trigger filter saat halaman dimuat (jika ada sesi terpilih sebelumnya)
    if (jamSelect.value) {
        filterGuru();
    }

    // Loading state submit
    document.getElementById('formPengganti')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('btnTugaskan');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Memproses...';
    });
</script>
@endpush