@extends('layouts.app')

@section('title', 'Mata Pelajaran')

@section('content')
<x-page-header
    title="Mata Pelajaran"
    subtitle="Kelola daftar mapel dan kelompok mapel."
    :create-route="route('mapel.create')"
    create-label="Tambah Mapel"
/>

<form method="GET" action="{{ route('mapel.index') }}" class="em-filter-bar">
    <input type="search" name="search" value="{{ request('search') }}" class="form-control em-filter-search" placeholder="Cari mata pelajaran atau kelompok">
    <button type="submit" class="btn btn-outline-primary">
        <i class="fas fa-search"></i>
        <span>Cari</span>
    </button>
    @if (request('search'))
        <a href="{{ route('mapel.index') }}" class="btn btn-outline-secondary">Reset</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Mata Pelajaran</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mapels as $index => $item)
                    <tr class="mapel-row">
                        <td>{{ $index + 1 + ($mapels->currentPage() - 1) * $mapels->perPage() }}</td>
                        <td>{{ $item->nama_mapel }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('mapel.edit', $item) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('mapel.destroy', $item) }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state colspan="3" title="Belum ada data mata pelajaran" description="Mata pelajaran yang cocok dengan filter akan tampil di sini." icon="fa-book-open" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $mapels->links() }}
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(url) {
    if (confirm('Yakin ingin menghapus mata pelajaran ini? Data jadwal yang terkait akan ikut terhapus.')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection
