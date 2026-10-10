@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
<x-page-header title="Data Siswa">
    Kelola data seluruh siswa {{ $madrasah->nama }}.
    <x-slot:actions>
        <a href="{{ route('ekspor.siswa') }}" class="btn btn-outline-secondary me-2">
            <i class="fas fa-file-export me-2"></i>Ekspor Excel
        </a>
        <a href="{{ route('ekspor.emis') }}" class="btn btn-outline-secondary me-2" title="Data lengkap (identitas, alamat, orang tua) siswa aktif untuk entri Dapodik/EMIS">
            <i class="fas fa-file-excel me-2"></i>Ekspor EMIS
        </a>
        <a href="{{ route('impor.index', 'siswa') }}" class="btn btn-outline-primary me-2">
            <i class="fas fa-file-import me-2"></i>Impor Excel
        </a>
        <a href="{{ route('siswa.create') }}" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Tambah Siswa
        </a>
    </x-slot:actions>
</x-page-header>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <form action="{{ route('siswa.index') }}" method="GET" class="row g-2">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari NIS, NISN, atau Nama..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-secondary w-100">Filter</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table data-hide-sm="1 4 5 6" class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 60px;">No</th>
                        <th>NIS / NISN</th>
                        <th>Nama Lengkap</th>
                        <th>Kelas</th>
                        <th>L/P</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswa as $index => $s)
                    <tr>
                        <td class="ps-4 text-muted small">{{ $siswa->firstItem() + $index }}</td>
                        <td>
                            <div class="fw-bold text-primary">{{ $s->nis }}</div>
                            <div class="small text-muted">{{ $s->nisn ?? '-' }}</div>
                        </td>
                        <td>
                            <div class="em-identity">
                                <x-avatar :name="$s->nama_lengkap" />
                                <div>
                                    <div class="fw-bold">{{ $s->nama_lengkap }}</div>
                                    <div class="small text-muted">{{ $s->tempat_lahir }}, {{ $s->tanggal_lahir?->format('d/m/Y') ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $s->kelas->nama_kelas ?? '-' }}</span></td>
                        <td><span class="badge {{ $s->jenis_kelamin == 'L' ? 'bg-info' : 'bg-danger' }} bg-opacity-10 {{ $s->jenis_kelamin == 'L' ? 'text-info' : 'text-danger' }}">{{ $s->jenis_kelamin }}</span></td>
                        <td>
                            <span class="badge {{ $s->status == 'Aktif' ? 'bg-success' : 'bg-warning' }} rounded-pill">
                                {{ $s->status ?? 'Aktif' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <a href="{{ route('siswa.edit', $s) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('siswa.destroy', $s) }}', {{ Js::from($s->nama_lengkap) }})" title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-row :colspan="7">Data siswa tidak ditemukan.</x-empty-row>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 py-3">
        {{ $siswa->appends(request()->query())->links() }}
    </div>
</div>
@endsection
