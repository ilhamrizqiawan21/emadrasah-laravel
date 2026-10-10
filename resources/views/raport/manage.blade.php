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

<form action="{{ route('raport.pelengkap', $siswa) }}" method="POST" class="mt-4">
    @csrf
    <input type="hidden" name="tahun_pelajaran_id" value="{{ $selectedTp }}">
    <input type="hidden" name="semester" value="{{ $semester }}">

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0">
            <h5 class="mb-0 fw-bold"><i class="fas fa-puzzle-piece me-2 text-primary"></i>Ekstrakurikuler, Kehadiran, dan Catatan Wali</h5>
        </div>
        <div class="card-body">
            <h6 class="fw-bold">Ekstrakurikuler</h6>
            @php($barisEkskul = max(3, $ekskul->count() + 1))
            @for($i = 0; $i < min($barisEkskul, 8); $i++)
            @php($e = $ekskul[$i] ?? null)
            <div class="row g-2 mb-2">
                <div class="col-md-5"><input type="text" name="ekskul[{{ $i }}][nama]" class="form-control" maxlength="150" placeholder="Nama ekskul" value="{{ old("ekskul.$i.nama", $e->nama_ekskul ?? '') }}" aria-label="Nama ekskul {{ $i + 1 }}"></div>
                <div class="col-md-2"><input type="text" name="ekskul[{{ $i }}][nilai]" class="form-control" maxlength="30" placeholder="Nilai (A/B/C)" value="{{ old("ekskul.$i.nilai", $e->nilai ?? '') }}" aria-label="Nilai ekskul {{ $i + 1 }}"></div>
                <div class="col-md-5"><input type="text" name="ekskul[{{ $i }}][keterangan]" class="form-control" maxlength="255" placeholder="Keterangan" value="{{ old("ekskul.$i.keterangan", $e->keterangan ?? '') }}" aria-label="Keterangan ekskul {{ $i + 1 }}"></div>
            </div>
            @endfor
            <small class="text-muted d-block mb-4">Baris nama kosong dilewati. Simpan lalu buka lagi untuk menambah baris (maksimal 8).</small>

            <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
                <h6 class="fw-bold mb-0">Ketidakhadiran (hari)</h6>
                <a href="{{ route('raport.manage', [$siswa, 'tahun_pelajaran_id' => $selectedTp, 'semester' => $semester, 'hitung' => 1]) }}" class="btn btn-sm btn-outline-secondary"
                   onclick="return confirm('Isi ulang dari absensi siswa harian? Perubahan nilai di halaman ini yang belum disimpan akan hilang.')">
                    <i class="fas fa-rotate me-1"></i> Isi ulang dari absensi
                </a>
            </div>
            <div class="row g-2 mb-1">
                @foreach(['sakit' => 'Sakit', 'ijin' => 'Izin', 'tanpa_keterangan' => 'Tanpa keterangan'] as $kolom => $label)
                <div class="col-md-4">
                    <label class="form-label small fw-bold text-uppercase text-muted" for="{{ $kolom }}">{{ $label }}</label>
                    <input type="number" min="0" max="366" name="{{ $kolom }}" id="{{ $kolom }}" class="form-control" value="{{ old($kolom, $kehadiran[$kolom]) }}">
                </div>
                @endforeach
            </div>
            <small class="text-muted d-block mb-4">
                {{ $kehadiranOtomatis ? 'Angka dihitung otomatis dari absensi siswa harian pada semester ini; simpan untuk menetapkannya.' : 'Angka tersimpan. Gunakan "Isi ulang dari absensi" untuk menghitung ulang.' }}
            </small>

            <h6 class="fw-bold">Catatan Wali Kelas</h6>
            <textarea name="catatan_wali" class="form-control" rows="3" maxlength="2000" placeholder="Catatan untuk siswa dan orang tua">{{ old('catatan_wali', $catatan) }}</textarea>
        </div>
        <div class="card-footer bg-white text-end py-3">
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i>Simpan Ekskul, Kehadiran, dan Catatan</button>
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
