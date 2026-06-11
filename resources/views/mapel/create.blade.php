@extends('layouts.app')

@section('title', 'Tambah Mata Pelajaran')

@section('content')
<div class="mb-4">
    <a href="{{ route('mapel.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Form Tambah Mata Pelajaran</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('mapel.store') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                <input type="text" name="nama_mapel" class="form-control @error('nama_mapel') is-invalid @enderror" value="{{ old('nama_mapel') }}" required>
                @error('nama_mapel')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Contoh: Matematika, Bahasa Indonesia, IPA, dll.</small>
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