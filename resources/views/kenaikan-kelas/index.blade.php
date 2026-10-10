@extends('layouts.app')

@section('title', 'Kenaikan Kelas')

@section('content')
<x-page-header title="Kenaikan Kelas & Kelulusan">
    Proses akhir tahun per kelas. Tiap siswa hanya dapat diproses satu kali per tahun pelajaran, sehingga urutan kelas tidak masalah.
</x-page-header>

<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <form method="GET" action="{{ route('kenaikan-kelas.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold" for="kelas_id">Kelas asal</label>
                <select name="kelas_id" id="kelas_id" class="form-select">
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" @selected($kelas?->id === $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold" for="tahun_pelajaran_id">Tahun pelajaran yang diselesaikan</label>
                <select name="tahun_pelajaran_id" id="tahun_pelajaran_id" class="form-select">
                    @foreach($tahunList as $t)
                        <option value="{{ $t->id }}" @selected($tahun?->id === $t->id)>{{ $t->kode }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

@if(! $kelas || ! $tahun)
    <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">
        <p class="mb-0">Belum ada kelas atau tahun pelajaran. Tambahkan lebih dulu di Master Data.</p>
    </div></div>
@else
<form method="POST" action="{{ route('kenaikan-kelas.store') }}" id="formKenaikan" onsubmit="return confirm('Proses siswa sesuai pilihan? Hasil bisa dibatalkan satu per satu selama data siswa belum diubah.')">
    @csrf
    <input type="hidden" name="kelas_asal_id" value="{{ $kelas->id }}">
    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahun->id }}">

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-3">
            <h5 class="mb-0 fw-semibold">{{ $kelas->nama_kelas }} · {{ $tahun->kode }}</h5>
            @if($siswa->isNotEmpty())
            <div class="d-flex align-items-center gap-2">
                <label for="kelas_tujuan_id" class="form-label mb-0 fw-semibold">Kelas tujuan (untuk yang naik)</label>
                <select name="kelas_tujuan_id" id="kelas_tujuan_id" class="form-select form-select-sm w-auto @error('kelas_tujuan_id') is-invalid @enderror">
                    <option value="">— pilih —</option>
                    @foreach($kelasList->where('id', '!=', $kelas->id) as $k)
                        <option value="{{ $k->id }}" @selected(old('kelas_tujuan_id') == $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
        @error('kelas_tujuan_id')<div class="alert alert-danger rounded-0 mb-0">{{ $message }}</div>@enderror
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-stack-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr><th width="50" class="text-center">No</th><th>NIS</th><th>Nama Siswa</th><th width="200">Hasil</th></tr>
                    </thead>
                    <tbody>
                        @forelse($siswa as $i => $s)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $s->nis }}</td>
                            <td class="fw-semibold">{{ $s->nama_lengkap }}</td>
                            <td data-label="Hasil">
                                <select name="aksi[{{ $s->id }}]" class="form-select form-select-sm" aria-label="Hasil {{ $s->nama_lengkap }}">
                                    @foreach(['naik' => 'Naik kelas', 'tinggal' => 'Tinggal kelas', 'lulus' => 'Lulus', 'tunda' => 'Tunda (jangan diproses)'] as $val => $label)
                                        <option value="{{ $val }}" @selected(old("aksi.$s->id", 'naik') === $val)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        @empty
                        <x-empty-row :colspan="4" icon="fa-circle-check">Tidak ada siswa aktif yang belum diproses di kelas ini.</x-empty-row>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($siswa->isNotEmpty())
        <div class="card-footer bg-white d-flex flex-wrap justify-content-between align-items-center gap-2 py-3">
            <small class="text-muted">Siswa yang naik dipindahkan ke kelas tujuan. Yang lulus berstatus Lulus dan tercatat di buku induk.</small>
            <button type="submit" class="btn btn-primary px-4">
                <i class="fas fa-check me-2"></i> Proses
            </button>
        </div>
        @endif
    </div>
</form>

@if($diproses->isNotEmpty())
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-semibold">Sudah diproses dari kelas ini</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-stack-sm align-middle mb-0">
                <thead class="table-light"><tr><th>Nama Siswa</th><th>Hasil</th><th>Kelas tujuan</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @foreach($diproses as $r)
                    <tr>
                        <td class="fw-semibold">{{ $r->siswa?->nama_lengkap ?? '—' }}</td>
                        <td data-label="Hasil"><span class="badge bg-{{ ['naik' => 'success', 'tinggal' => 'warning', 'lulus' => 'primary'][$r->hasil] }}">{{ ucfirst($r->hasil) }}</span></td>
                        <td data-label="Kelas tujuan">{{ $r->kelas_tujuan_nama ?? '—' }}</td>
                        <td class="text-end">
                            <form method="POST" action="{{ route('kenaikan-kelas.batal', $r) }}" class="d-inline" onsubmit="return confirm('Batalkan proses untuk siswa ini?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">Batalkan</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endif
@endif
@endsection
