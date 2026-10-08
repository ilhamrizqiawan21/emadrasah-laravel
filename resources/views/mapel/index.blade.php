@extends('layouts.app')

@section('title', 'Mata Pelajaran')

@section('content')
<x-page-header title="Mata Pelajaran">
    Kelola mata pelajaran. Tambah mapel baru lewat form di bawah ini.
</x-page-header>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="fas fa-plus-circle me-1"></i> Tambah Mata Pelajaran
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('mapel.store') }}">
            @csrf
            <div class="row g-3 align-items-start">
                <div class="col-md-9">
                    <label class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                    <input type="text" name="nama_mapel" class="form-control @error('nama_mapel') is-invalid @enderror" value="{{ old('nama_mapel') }}" required>
                    @error('nama_mapel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Contoh: Matematika, Bahasa Indonesia, IPA, dll.</div>
                </div>
                <div class="col-md-3 text-md-end" style="padding-top: 32px;">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

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
                    <x-empty-row :colspan="3" icon="fa-folder-open">Belum ada data mata pelajaran</x-empty-row>
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