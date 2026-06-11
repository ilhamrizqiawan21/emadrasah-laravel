@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="em-page-title">Buku Induk Register Siswa</h2>
            <p class="text-muted">Manajemen data lengkap siswa standar Kurikulum Merdeka.</p>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('buku-induk.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Siswa Baru
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">No. Urut</th>
                            <th>NIS/NISN</th>
                            <th>Nama Lengkap</th>
                            <th>Kelas</th>
                            <th>Tahun Masuk</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa as $s)
                        <tr>
                            <td class="ps-4">{{ $s->no_urut ?? '-' }}</td>
                            <td>
                                <div class="fw-bold">{{ $s->nis }}</div>
                                <small class="text-muted">NISN: {{ $s->nisn ?? '-' }}</small>
                            </td>
                            <td>{{ $s->nama_lengkap }}</td>
                            <td>{{ $s->kelas->nama_kelas ?? '-' }}</td>
                            <td>{{ $s->tahunPelajaran->kode ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $s->status == 'Aktif' ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $s->status }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('buku-induk.show', $s) }}" class="btn btn-sm btn-info text-white" title="Lihat Profil">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('buku-induk.edit', $s) }}" class="btn btn-sm btn-warning text-white" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('buku-induk.destroy', $s) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Hapus data ini?')" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">Belum ada data siswa.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($siswa->hasPages())
        <div class="card-footer bg-white border-0">
            {{ $siswa->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
