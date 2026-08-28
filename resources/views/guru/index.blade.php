@extends('layouts.app')

@section('title', 'Data Guru')

@section('content')
<x-page-header
    title="Data Guru"
    subtitle="Kelola identitas guru, NIP, dan beban mengajar."
    :create-route="route('guru.create')"
    create-label="Tambah Guru"
/>

<form method="GET" action="{{ route('guru.index') }}" class="em-filter-bar">
    <input type="search" name="search" value="{{ request('search') }}" class="form-control em-filter-search" placeholder="Cari kode, NIP, nama, atau bidang studi">
    <button type="submit" class="btn btn-outline-primary">
        <i class="fas fa-search"></i>
        <span>Cari</span>
    </button>
    @if (request('search'))
        <a href="{{ route('guru.index') }}" class="btn btn-outline-secondary">Reset</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Kode</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Bidang Studi</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gurus as $index => $guru)
                    <tr>
                        <td>{{ $index + 1 + ($gurus->currentPage() - 1) * $gurus->perPage() }}</td>
                        <td><span class="badge bg-secondary">{{ $guru->kode }}</span></td>
                        <td>{{ $guru->nip ?? '-' }}</td>
                        <td>{{ $guru->nama }}</td>
                        <td>{{ $guru->bidang_studi ?? '-' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('guru.edit', $guru) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('guru.destroy', $guru) }}', '{{ $guru->nama }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <x-empty-state colspan="6" title="Belum ada data guru" description="Data guru yang cocok dengan filter akan tampil di sini." icon="fa-chalkboard-user" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $gurus->links() }}
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(url, name) {
    if (confirm('Yakin ingin menghapus guru "' + name + '"?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection
