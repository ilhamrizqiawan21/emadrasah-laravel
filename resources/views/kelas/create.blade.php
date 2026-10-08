@extends('layouts.app')

@section('title', 'Tambah Kelas')

@section('content')
<div class="mb-4">
    <a href="{{ route('kelas.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Form Tambah Kelas</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('kelas.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                    <input type="text" name="nama_kelas" class="form-control @error('nama_kelas') is-invalid @enderror" value="{{ old('nama_kelas') }}" required>
                    @error('nama_kelas')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="text-muted">Contoh: 7A, 8B, 9C</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                    <select name="tingkat" class="form-select @error('tingkat') is-invalid @enderror" required>
                        <option value="">-- Pilih Tingkat --</option>
                        <option value="7" {{ old('tingkat') == '7' ? 'selected' : '' }}>7 (Kelas 7)</option>
                        <option value="8" {{ old('tingkat') == '8' ? 'selected' : '' }}>8 (Kelas 8)</option>
                        <option value="9" {{ old('tingkat') == '9' ? 'selected' : '' }}>9 (Kelas 9)</option>
                    </select>
                    @error('tingkat')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Wali Kelas</label>
                    <select name="guru_pembimbing_id" class="form-select @error('guru_pembimbing_id') is-invalid @enderror">
                        <option value="">-- Pilih Wali Kelas --</option>
                        @foreach($gurus as $guru)
                            <option value="{{ $guru->id }}" {{ old('guru_pembimbing_id') == $guru->id ? 'selected' : '' }}>
                                {{ $guru->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('guru_pembimbing_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Kapasitas</label>
                    <input type="number" name="kapasitas" class="form-control @error('kapasitas') is-invalid @enderror" value="{{ old('kapasitas', 40) }}">
                    @error('kapasitas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Ruangan</label>
                    <input type="text" name="ruangan" class="form-control @error('ruangan') is-invalid @enderror" value="{{ old('ruangan') }}">
                    @error('ruangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
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