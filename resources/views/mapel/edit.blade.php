@extends('layouts.app')

@section('title', 'Edit Mata Pelajaran')

@section('content')
<x-page-header :title="'Edit Mata Pelajaran: '.($mapel->nama_mapel)">
    <x-slot:actions>
        <a href="{{ route('mapel.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('mapel.update', $mapel) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" name="nama_mapel" class="form-control @error('nama_mapel') is-invalid @enderror" value="{{ old('nama_mapel', $mapel->nama_mapel) }}" required>
                        @error('nama_mapel')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger bg-opacity-10 border-danger">
            <div class="card-body">
                <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Zona Berbahaya</h6>
                <p class="small">Menghapus mata pelajaran akan menghapus semua jadwal yang menggunakan mapel ini.</p>
                <button class="btn btn-danger btn-sm" onclick="confirmDelete('{{ route('mapel.destroy', $mapel) }}')">
                    <i class="fas fa-trash"></i> Hapus Mapel Ini
                </button>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(url) {
    if (confirm('Yakin ingin menghapus mata pelajaran ini? Semua data jadwal terkait akan ikut terhapus.')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection