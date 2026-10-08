@extends('layouts.app')

@section('title', 'Data Guru')

@section('content')
<x-page-header title="Data Guru">
    <x-slot:actions>
        <a href="{{ route('guru.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Tambah Guru
        </a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table data-hide-sm="1 3 5" class="table table-hover mb-0">
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
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('guru.destroy', $guru) }}', {{ Js::from($guru->nama) }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <x-empty-row :colspan="5" icon="fa-folder-open">Belum ada data guru</x-empty-row>
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