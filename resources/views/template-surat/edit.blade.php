@extends('layouts.app')

@section('title', 'Edit Template Surat')

@section('content')
<div class="mb-4">
    <a href="{{ route('template-surat.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5>Edit Template: {{ $templateSurat->nama_template }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('template-surat.update', $templateSurat) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Nama Template <span class="text-danger">*</span></label>
                        <input type="text" name="nama_template" class="form-control @error('nama_template') is-invalid @enderror" value="{{ old('nama_template', $templateSurat->nama_template) }}" required>
                        @error('nama_template')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konten Template <span class="text-danger">*</span></label>
                        <textarea name="konten" class="form-control @error('konten') is-invalid @enderror" rows="10" required>{{ old('konten', $templateSurat->konten) }}</textarea>
                        @error('konten')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                <p class="small">Menghapus template tidak akan mempengaruhi surat yang sudah dibuat.</p>
                <button class="btn btn-danger btn-sm" onclick="confirmDelete('{{ route('template-surat.destroy', $templateSurat) }}')">
                    <i class="fas fa-trash"></i> Hapus Template Ini
                </button>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

<script>
function confirmDelete(url) {
    if (confirm('Yakin ingin menghapus template ini?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection