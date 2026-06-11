@extends('layouts.app')

@section('title', 'Tambah Surat Masuk')

@section('content')
<div class="mb-4">
    <a href="{{ route('surat-masuk.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Form Tambah Surat Masuk</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('surat-masuk.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Asal Surat <span class="text-danger">*</span></label>
                    <input type="text" name="asal_surat" class="form-control @error('asal_surat') is-invalid @enderror" value="{{ old('asal_surat') }}" required>
                    @error('asal_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor Surat</label>
                    <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror" value="{{ old('nomor_surat') }}">
                    @error('nomor_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Perihal <span class="text-danger">*</span></label>
                    <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror" value="{{ old('perihal') }}" required>
                    @error('perihal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Terima <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_terima" class="form-control @error('tanggal_terima') is-invalid @enderror" value="{{ old('tanggal_terima', date('Y-m-d')) }}" required>
                    @error('tanggal_terima')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" class="form-control @error('tanggal_surat') is-invalid @enderror" value="{{ old('tanggal_surat') }}">
                    @error('tanggal_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">File Scan (PDF/JPG)</label>
                    <input type="file" name="file_scan" class="form-control @error('file_scan') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                    @error('file_scan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Maksimal 2MB</small>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Disposisi (opsional)</label>
                    <textarea name="disposisi" class="form-control @error('disposisi') is-invalid @enderror" rows="3">{{ old('disposisi') }}</textarea>
                    @error('disposisi')<div class="invalid-feedback">{{ $message }}</div>@enderror
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