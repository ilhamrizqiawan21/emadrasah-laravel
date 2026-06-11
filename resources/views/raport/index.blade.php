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

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
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
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-user-slash fa-3x mb-3 opacity-25"></i>
                            <p>Data siswa tidak ditemukan.</p>
                        </td>
                    </tr>
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
