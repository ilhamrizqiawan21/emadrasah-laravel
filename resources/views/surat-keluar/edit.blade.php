@extends('layouts.app')

@section('title', 'Edit Surat Keluar')

@section('content')
<div class="mb-4">
    <a href="{{ route('surat-keluar.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Edit Surat Keluar: {{ $suratKeluar->nomor_surat }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('surat-keluar.update', $suratKeluar) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror" value="{{ old('nomor_surat', $suratKeluar->nomor_surat) }}" required>
                    @error('nomor_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tujuan <span class="text-danger">*</span></label>
                    <input type="text" name="tujuan" class="form-control @error('tujuan') is-invalid @enderror" value="{{ old('tujuan', $suratKeluar->tujuan) }}" required>
                    @error('tujuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Perihal <span class="text-danger">*</span></label>
                    <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror" value="{{ old('perihal', $suratKeluar->perihal) }}" required>
                    @error('perihal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Kirim <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_kirim" class="form-control @error('tanggal_kirim') is-invalid @enderror" value="{{ old('tanggal_kirim', $suratKeluar->tanggal_kirim->format('Y-m-d')) }}" required>
                    @error('tanggal_kirim')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Lampiran</label>
                    <input type="text" name="lampiran" class="form-control @error('lampiran') is-invalid @enderror" value="{{ old('lampiran', $suratKeluar->lampiran) }}">
                    @error('lampiran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">File Draft</label>
                    @if($suratKeluar->file_draft)
                        <div class="mb-2">
                            <a href="{{ route('files.show', ['path' => $suratKeluar->file_draft]) }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-download"></i> File Saat Ini
                            </a>
                        </div>
                    @endif
                    <input type="file" name="file_draft" class="form-control @error('file_draft') is-invalid @enderror" accept=".pdf,.doc,.docx">
                    @error('file_draft')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti file</small>
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection