@extends('layouts.app')

@section('title', 'Tambah Template Surat')

@section('content')
<div class="mb-4">
    <a href="{{ route('template-surat.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Form Tambah Template Surat</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('template-surat.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nama Template <span class="text-danger">*</span></label>
                <input type="text" name="nama_template" class="form-control @error('nama_template') is-invalid @enderror" value="{{ old('nama_template') }}" required>
                @error('nama_template')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">Contoh: Undangan Rapat, Surat Tugas, dll.</small>
            </div>
            <div class="mb-3">
                <label class="form-label">Konten Template <span class="text-danger">*</span></label>
                <textarea name="konten" class="form-control @error('konten') is-invalid @enderror" rows="10" required>{{ old('konten') }}</textarea>
                @error('konten')<div class="invalid-feedback">{{ $message }}</div>@enderror
                <small class="text-muted">
                    Gunakan placeholder seperti: [TANGGAL], [NAMA], [NOMOR_SURAT] untuk dinamis.
                </small>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection