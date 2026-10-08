@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<x-page-header title="Data Kelas">
    <x-slot:actions>
        <a href="{{ route('kelas.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Kelas
        </a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table data-hide-sm="1 3 5 6" class="table table-hover mb-0">
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
                    <x-empty-row :colspan="4" icon="fa-folder-open">Belum ada data kelas</x-empty-row>
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