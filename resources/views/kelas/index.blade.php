@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<x-page-header
    title="Data Kelas"
    subtitle="Kelola rombel, wali kelas, ruangan, dan kapasitas."
    :create-route="route('kelas.create')"
    create-label="Tambah Kelas"
/>

<form method="GET" action="{{ route('kelas.index') }}" class="em-filter-bar">
    <input type="search" name="search" value="{{ request('search') }}" class="form-control em-filter-search" placeholder="Cari kelas, tingkat, wali kelas, atau ruangan">
    <button type="submit" class="btn btn-outline-primary">
        <i class="fas fa-search"></i>
        <span>Cari</span>
    </button>
    @if (request('search'))
        <a href="{{ route('kelas.index') }}" class="btn btn-outline-secondary">Reset</a>
    @endif
</form>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Kelas</th>
                        <th>Tingkat</th>
                        <th>Wali Kelas</th>
                        <th>Ruangan</th>
                        <th>Kapasitas</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas as $index => $item)
                    <tr class="kelas-row">
                        <td>{{ $index + 1 + ($kelas->currentPage() - 1) * $kelas->perPage() }}</td>
                        <td>
                            <span class="badge bg-primary" style="font-size:14px;">{{ $item->nama_kelas }}</span>
                        </td>
                        <td>{{ $item->tingkat }}</td>
                        <td>{{ $item->guruPembimbing->nama ?? '-' }}</td>
                        <td>{{ $item->ruangan ?? '-' }}</td>
                        <td>{{ $item->kapasitas ?? '0' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('kelas.edit', $item) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('kelas.destroy', $item) }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-state colspan="7" title="Belum ada data kelas" description="Data kelas yang cocok dengan filter akan tampil di sini." icon="fa-door-open" />
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $kelas->links() }}
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(url) {
    if (confirm('Yakin ingin menghapus kelas ini? Semua data jadwal yang terkait dengan kelas ini akan ikut terhapus.')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection
