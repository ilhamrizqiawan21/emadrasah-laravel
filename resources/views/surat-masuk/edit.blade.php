@extends('layouts.app')

@section('title', 'Edit Surat Masuk')

@section('content')
<x-page-header :title="'Edit Surat Masuk: '.($suratMasuk->nomor_agenda)">
    <x-slot:actions>
        <a href="{{ route('surat-masuk.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('surat-masuk.update', $suratMasuk) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Asal Surat <span class="text-danger">*</span></label>
                    <input type="text" name="asal_surat" class="form-control @error('asal_surat') is-invalid @enderror" value="{{ old('asal_surat', $suratMasuk->asal_surat) }}" required>
                    @error('asal_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor Surat</label>
                    <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror" value="{{ old('nomor_surat', $suratMasuk->nomor_surat) }}">
                    @error('nomor_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Perihal <span class="text-danger">*</span></label>
                    <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror" value="{{ old('perihal', $suratMasuk->perihal) }}" required>
                    @error('perihal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Terima <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_terima" class="form-control @error('tanggal_terima') is-invalid @enderror" value="{{ old('tanggal_terima', $suratMasuk->tanggal_terima->format('Y-m-d')) }}" required>
                    @error('tanggal_terima')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Surat</label>
                    <input type="date" name="tanggal_surat" class="form-control @error('tanggal_surat') is-invalid @enderror" value="{{ old('tanggal_surat', $suratMasuk->tanggal_surat ? $suratMasuk->tanggal_surat->format('Y-m-d') : '') }}">
                    @error('tanggal_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="diterima" {{ old('status', $suratMasuk->status) == 'diterima' ? 'selected' : '' }}>Diterima</option>
                        <option value="diproses" {{ old('status', $suratMasuk->status) == 'diproses' ? 'selected' : '' }}>Diproses</option>
                        <option value="selesai" {{ old('status', $suratMasuk->status) == 'selesai' ? 'selected' : '' }}>Selesai</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">File Scan</label>
                    @if($suratMasuk->file_scan)
                        <div class="mb-2">
                            <a href="{{ route('files.show', ['path' => $suratMasuk->file_scan]) }}" target="_blank" class="btn btn-sm btn-info">
                                <i class="fas fa-download"></i> Lihat File Saat Ini
                            </a>
                        </div>
                    @endif
                    <input type="file" name="file_scan" class="form-control @error('file_scan') is-invalid @enderror" accept=".pdf,.jpg,.jpeg,.png">
                    @error('file_scan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Kosongkan jika tidak ingin mengganti file</small>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Disposisi</label>
                    <textarea name="disposisi" class="form-control @error('disposisi') is-invalid @enderror" rows="3">{{ old('disposisi', $suratMasuk->disposisi) }}</textarea>
                    @error('disposisi')<div class="invalid-feedback">{{ $message }}</div>@enderror
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