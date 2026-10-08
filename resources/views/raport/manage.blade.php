@extends('layouts.app')

@section('title', 'Kelola Nilai Raport')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="em-page-title">Kelola Nilai Raport</h2>
        <div class="d-flex align-items-center gap-3">
            <div class="avatar-circle bg-primary text-white fw-bold" style="width: 48px; height: 48px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                {{ substr($siswa->nama_lengkap, 0, 1) }}
            </div>
            <div>
                <h5 class="mb-0 fw-bold">{{ $siswa->nama_lengkap }}</h5>
                <p class="text-muted mb-0 small">NIS: {{ $siswa->nis }} | Kelas: {{ $siswa->kelas->nama_kelas ?? '-' }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('raport.export-pdf', [$siswa, 'tahun_pelajaran_id' => $selectedTp, 'semester' => $semester]) }}" target="_blank" class="btn btn-danger me-2">
            <i class="fas fa-file-pdf me-1"></i> Cetak Raport
        </a>
        <a href="{{ route('raport.index') }}" class="btn btn-light border px-4">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form action="{{ route('raport.manage', $siswa) }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-uppercase text-muted">Tahun Pelajaran</label>
                <select name="tahun_pelajaran_id" class="form-select border-0 bg-light" data-prev="{{ $selectedTp }}" onchange="gantiPeriode(this)">
                    @foreach($tp as $t)
                    <option value="{{ $t->id }}" {{ $selectedTp == $t->id ? 'selected' : '' }}>{{ $t->kode }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase text-muted">Semester</label>
                <select name="semester" class="form-select border-0 bg-light" data-prev="{{ $semester }}" onchange="gantiPeriode(this)">
                    <option value="1" {{ $semester == 1 ? 'selected' : '' }}>1 (Ganjil)</option>
                    <option value="2" {{ $semester == 2 ? 'selected' : '' }}>2 (Genap)</option>
                </select>
            </div>
            <div class="col-md-5 text-end">
                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2">
                    <i class="fas fa-info-circle me-1"></i> Nilai tersimpan setelah klik "Simpan Perubahan Nilai"
                </span>
            </div>
        </form>
    </div>
</div>

<form action="{{ route('raport.store', $siswa) }}" method="POST" id="formNilai">
    @csrf
    <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTp }}">
    <input type="hidden" name="semester" value="{{ $semester }}">

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list-check me-2 text-primary"></i>Daftar Nilai Mata Pelajaran</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-stack-sm table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" width="30%">Mata Pelajaran</th>
                            <th width="15%">Nilai Akhir</th>
                            <th>Capaian Kompetensi / Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mapels as $m)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $m->nama_mapel }}</div>
                                <div class="small text-muted">{{ $m->kelompok ?? 'Kelompok A' }}</div>
                            </td>
                            <td data-label="Nilai Akhir">
                                <input type="number" name="nilai[{{ $m->id }}][angka]" class="form-control fw-bold text-center border-0 bg-light" 
                                       value="{{ $nilai[$m->id]->nilai_akhir ?? '' }}" min="0" max="100" placeholder="0">
                            </td>
                            <td data-label="Capaian Kompetensi / Deskripsi">
                                <textarea name="nilai[{{ $m->id }}][capaian]" class="form-control border-0 bg-light" rows="3" 
                                          placeholder="Contoh: Menunjukkan penguasaan yang sangat baik dalam...">{{ $nilai[$m->id]->deskripsi ?? '' }}</textarea>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white text-end py-3 em-sticky-save">
            <span id="catatanBelumSimpan" class="text-warning-emphasis small me-3 d-none">
                <i class="fas fa-circle-exclamation me-1"></i>Ada perubahan yang belum disimpan
            </span>
            <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm">
                <i class="fas fa-cloud-arrow-up me-2"></i>Simpan Perubahan Nilai
            </button>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
(function () {
    const form = document.getElementById('formNilai');
    const catatan = document.getElementById('catatanBelumSimpan');
    let belumSimpan = false, sedangSimpan = false;

    form.addEventListener('input', () => {
        belumSimpan = true;
        catatan.classList.remove('d-none');
    });
    form.addEventListener('submit', () => { sedangSimpan = true; });

    // Peringatan bila halaman ditutup/ditinggalkan saat ada isian yang belum disimpan
    window.addEventListener('beforeunload', (e) => {
        if (belumSimpan && !sedangSimpan) { e.preventDefault(); e.returnValue = ''; }
    });

    // Ganti tahun pelajaran / semester memuat ulang halaman -> konfirmasi dulu
    window.gantiPeriode = function (el) {
        if (belumSimpan && !confirm('Ada nilai yang belum disimpan. Pindah periode dan buang perubahan?')) {
            el.value = el.dataset.prev;
            return;
        }
        sedangSimpan = true; // jangan munculkan peringatan ganda dari beforeunload
        el.form.submit();
    };
})();
</script>
@endpush
