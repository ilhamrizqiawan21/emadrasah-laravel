@extends('layouts.app')

@section('title', 'Input Nilai')

@section('content')
<x-page-header title="Input Nilai per Kelas">
    Isi nilai satu mata pelajaran untuk seluruh siswa dalam satu kelas.
</x-page-header>

<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <form method="GET" action="{{ route('nilai.index') }}" class="row g-3 align-items-end" id="formPilihNilai">
            <div class="col-md-3">
                <label class="form-label fw-semibold" for="kelas_id">Kelas</label>
                {{-- Ganti kelas: kirim tanpa mapel_id agar daftar mapel mengikuti kelas baru --}}
                <select name="kelas_id" id="kelas_id" class="form-select" onchange="this.form.elements.mapel_id.disabled = true; this.form.submit()">
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" @selected($kelas?->id === $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold" for="mapel_id">Mata Pelajaran</label>
                <select name="mapel_id" id="mapel_id" class="form-select">
                    @foreach($mapelList as $m)
                        <option value="{{ $m->id }}" @selected($mapel?->id === $m->id)>{{ $m->nama_mapel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold" for="tahun_pelajaran_id">Tahun Pelajaran</label>
                <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="form-select">
                    @foreach($tahunList as $t)
                        <option value="{{ $t->id }}" @selected($tahunId == $t->id)>{{ $t->kode }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label fw-semibold" for="semester">Smt</label>
                <select name="semester" id="semester" class="form-select">
                    <option value="1" @selected($semester === 1)>1</option>
                    <option value="2" @selected($semester === 2)>2</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

@if(! $kelas)
    <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">
        <i class="fas fa-school fa-3x mb-3 opacity-25"></i>
        <p class="mb-0">Belum ada kelas yang bisa Anda nilai. Nilai hanya bisa diisi untuk kelas dan mata pelajaran yang ada di jadwal mengajar Anda.</p>
    </div></div>
@elseif(! $mapel)
    <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">
        <p class="mb-0">Belum ada mata pelajaran yang bisa dinilai di kelas ini.</p>
    </div></div>
@elseif(! $tahunId)
    <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">
        <p class="mb-0">Belum ada tahun pelajaran aktif. Aktifkan satu di menu Tahun Pelajaran, atau pilih tahun pelajaran di atas.</p>
    </div></div>
@else
<form method="POST" action="{{ route('nilai.store') }}">
    @csrf
    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
    <input type="hidden" name="mapel_id" value="{{ $mapel->id }}">
    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunId }}">
    <input type="hidden" name="semester" value="{{ $semester }}">

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold">{{ $mapel->nama_mapel }} · {{ $kelas->nama_kelas }} · Semester {{ $semester }}</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-stack-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th width="140">Nilai (0-100)</th>
                            <th>Capaian / Deskripsi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa as $i => $s)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $s->nis }}</td>
                            <td class="fw-semibold">{{ $s->nama_lengkap }}</td>
                            <td data-label="Nilai">
                                <input type="number" name="nilai[{{ $s->id }}][angka]" class="form-control text-center fw-bold" min="0" max="100" step="1" inputmode="numeric"
                                       value="{{ old("nilai.$s->id.angka", $nilai[$s->id]->nilai_akhir ?? '') }}" aria-label="Nilai {{ $s->nama_lengkap }}">
                            </td>
                            <td data-label="Capaian">
                                <input type="text" name="nilai[{{ $s->id }}][capaian]" class="form-control" maxlength="1000"
                                       value="{{ old("nilai.$s->id.capaian", $nilai[$s->id]->deskripsi ?? '') }}" placeholder="Opsional" aria-label="Capaian {{ $s->nama_lengkap }}">
                            </td>
                        </tr>
                        @empty
                        <x-empty-row :colspan="5" icon="fa-user-graduate">Belum ada siswa aktif di kelas ini.</x-empty-row>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($siswa->isNotEmpty())
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <small class="text-muted">Kolom nilai yang dikosongkan dilewati; nilai yang sudah tersimpan tidak terhapus.</small>
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Simpan Nilai</button>
        </div>
        @endif
    </div>
</form>
@endif
@endsection
