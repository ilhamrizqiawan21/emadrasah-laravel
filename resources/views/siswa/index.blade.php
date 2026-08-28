@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
<x-page-header
    title="Data Siswa"
    subtitle="Kelola data seluruh siswa MTs Al-Ihsan Batujajar."
    :create-route="route('siswa.create')"
    create-label="Tambah Siswa"
/>

<form action="{{ route('siswa.index') }}" method="GET" class="em-filter-bar">
    <input type="search" name="search" class="form-control em-filter-search" placeholder="Cari NIS, NISN, atau nama" value="{{ request('search') }}">
    <select name="status" class="form-select" style="max-width: 180px;">
        <option value="">Semua Status</option>
        @foreach (['Aktif', 'Lulus', 'Pindah', 'Keluar'] as $status)
            <option value="{{ $status }}" @selected(request('status') === $status)>{{ $status }}</option>
        @endforeach
    </select>
    <select name="kelas_id" class="form-select" style="max-width: 180px;">
        <option value="">Semua Kelas</option>
        @foreach ($kelas as $item)
            <option value="{{ $item->id }}" @selected((string) request('kelas_id') === (string) $item->id)>{{ $item->nama_kelas }}</option>
        @endforeach
    </select>
    <select name="tahun_pelajaran_id" class="form-select" style="max-width: 220px;">
        <option value="">Semua Tahun</option>
        @foreach ($tahunPelajaran as $item)
            <option value="{{ $item->id }}" @selected((string) request('tahun_pelajaran_id') === (string) $item->id)>{{ $item->kode }}</option>
        @endforeach
    </select>
    <button type="submit" class="btn btn-outline-primary">
        <i class="fas fa-filter"></i>
        <span>Filter</span>
    </button>
    @if (request()->hasAny(['search', 'status', 'kelas_id', 'tahun_pelajaran_id', 'sort']))
        <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary">Reset</a>
    @endif
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 60px;">No</th>
                        <th><x-sort-link column="nis" label="NIS / NISN" /></th>
                        <th><x-sort-link column="nama" label="Nama Lengkap" /></th>
                        <th><x-sort-link column="kelas" label="Kelas" /></th>
                        <th>L/P</th>
                        <th><x-sort-link column="status" label="Status" /></th>
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
                            <div class="fw-bold">{{ $s->nama_lengkap }}</div>
                            <div class="small text-muted">{{ $s->tempat_lahir }}, {{ $s->tanggal_lahir?->format('d/m/Y') ?? '-' }}</div>
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
                                <a href="{{ route('siswa.edit', $s) }}" class="btn btn-sm btn-outline-primary" title="Edit" aria-label="Edit siswa {{ $s->nama_lengkap }}">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('{{ route('siswa.destroy', $s) }}', '{{ $s->nama_lengkap }}')" title="Hapus" aria-label="Hapus siswa {{ $s->nama_lengkap }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state colspan="7" title="Data siswa tidak ditemukan" description="Ubah filter atau tambahkan siswa baru." icon="fa-user-graduate" />
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
