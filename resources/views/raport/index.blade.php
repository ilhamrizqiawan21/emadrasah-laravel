@extends('layouts.app')

@section('title', 'Arsip Nilai Raport')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-6">
        <h2 class="em-page-title">Arsip Nilai Raport</h2>
        <p class="text-muted">Kelola nilai akademik siswa untuk integrasi Buku Induk.</p>
    </div>
    <div class="col-md-6">
        <form action="{{ route('raport.index') }}" method="GET" class="input-group">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama siswa atau NIS..." value="{{ request('search') }}">
            <button class="btn btn-primary px-4" type="submit">Cari Siswa</button>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-bold mb-3"><i class="fas fa-print me-2 text-primary"></i>Cetak Raport Satu Kelas</h6>
        <form action="{{ route('raport.export-kelas') }}" method="GET" target="_blank" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-uppercase text-muted" for="cetak_kelas">Kelas</label>
                <select name="kelas_id" id="cetak_kelas" class="form-select" required>
                    @foreach($kelasList as $k)<option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-uppercase text-muted" for="cetak_tp">Tahun Pelajaran</label>
                <select name="tahun_pelajaran_id" id="cetak_tp" class="form-select" required>
                    @foreach($tahunList as $t)<option value="{{ $t->id }}" @selected($t->id === $tahunAktifId)>{{ $t->kode }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-uppercase text-muted" for="cetak_smt">Semester</label>
                <select name="semester" id="cetak_smt" class="form-select"><option value="1">1 (Ganjil)</option><option value="2">2 (Genap)</option></select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-danger flex-fill"><i class="fas fa-file-pdf me-1"></i> PDF</button>
                <button type="submit" formaction="{{ route('ekspor.nilai') }}" formtarget="_self" class="btn btn-outline-success flex-fill" title="Ekspor nilai satu kelas ke Excel"><i class="fas fa-file-excel me-1"></i> Excel</button>
            </div>
        </form>
    </div>
</div>

@if(auth()->user()->role === 'admin' && $tahunAktifId)
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h6 class="fw-bold mb-1"><i class="fas fa-unlock-keyhole me-2 text-primary"></i>Rilis Raport ke Wali Murid</h6>
        <p class="small text-muted">Wali murid dan siswa hanya melihat nilai dan PDF raport semester yang sudah dirilis (tahun pelajaran aktif).</p>
        <div class="d-flex flex-wrap gap-3">
            @foreach([1 => 'Ganjil', 2 => 'Genap'] as $smt => $nama)
                @php
                    $sudah = $dirilis->has($tahunAktifId.'-'.$smt);
                @endphp
                <form method="POST" action="{{ route('pengaturan.raport-rilis') }}" class="d-flex align-items-center gap-2">
                    @csrf
                    <input type="hidden" name="tahun_pelajaran_id" value="{{ $tahunAktifId }}">
                    <input type="hidden" name="semester" value="{{ $smt }}">
                    <input type="hidden" name="dirilis" value="{{ $sudah ? 0 : 1 }}">
                    <span>Semester {{ $smt }} ({{ $nama }})</span>
                    <span class="badge {{ $sudah ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">{{ $sudah ? 'Dirilis' : 'Belum dirilis' }}</span>
                    <button type="submit" class="btn btn-sm {{ $sudah ? 'btn-outline-danger' : 'btn-primary' }}">{{ $sudah ? 'Tarik' : 'Rilis' }}</button>
                </form>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table data-hide-sm="1 4" class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 60px;">No</th>
                        <th>Identitas Siswa</th>
                        <th>Kelas</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswa as $index => $s)
                    <tr>
                        <td class="ps-4 text-muted small">{{ $siswa->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-bold">{{ $s->nama_lengkap }}</div>
                            <div class="small text-primary fw-semibold">{{ $s->nis }}</div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $s->kelas->nama_kelas ?? '-' }}</span></td>
                        <td><span class="badge bg-success bg-opacity-10 text-success rounded-pill px-3">{{ $s->status ?? 'Aktif' }}</span></td>
                        <td class="text-end pe-4">
                            <a href="{{ route('raport.manage', $s) }}" class="btn btn-sm btn-primary px-3">
                                <i class="fas fa-edit me-1"></i> Kelola Nilai
                            </a>
                        </td>
                    </tr>
                    @empty
                    <x-empty-row :colspan="5" icon="fa-user-slash">Data siswa tidak ditemukan.</x-empty-row>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 py-3">
        {{ $siswa->links() }}
    </div>
</div>
@endsection
