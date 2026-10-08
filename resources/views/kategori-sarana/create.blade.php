@extends('layouts.app')

@section('title', 'Tambah Kategori Sarana')

@section('content')
<div class="mb-4">
    <a href="{{ route('kategori-sarana.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Tambah Kategori Sarana</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('kategori-sarana.store') }}">
            @csrf
            <div class="mb-3">
                <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" name="nama_kategori" class="form-control @error('nama_kategori') is-invalid @enderror" value="{{ old('nama_kategori') }}" required>
                @error('nama_kategori')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">Contoh: Elektronik, Furniture, Olahraga, dll.</div>
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection