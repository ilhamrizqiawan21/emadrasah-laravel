@extends('layouts.app')

@section('title', 'Tambah Surat Keluar')

@section('content')
<div class="mb-4">
    <a href="{{ route('surat-keluar.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Kembali
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Form Tambah Surat Keluar</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('surat-keluar.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-12 mb-3">
                    <label class="form-label">Otomatisasi Data Siswa (Opsional)</label>
                    <select id="selectSiswa" class="form-select border-primary shadow-sm">
                        <option value="">-- Pilih Siswa untuk mengisi Tujuan otomatis --</option>
                        @foreach($siswa as $s)
                        <option value="{{ $s->nama_lengkap }} (NIS: {{ $s->nis }})">{{ $s->nama_lengkap }} - {{ $s->nis }}</option>
                        @endforeach
                    </select>
                    <small class="text-muted">Memilih siswa akan otomatis mengisi kolom "Tujuan" di bawah.</small>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Nomor Surat <span class="text-danger">*</span></label>
                    <input type="text" name="nomor_surat" class="form-control @error('nomor_surat') is-invalid @enderror" value="{{ old('nomor_surat') }}" placeholder="Contoh: 001/MTs/XI/2025" required>
                    @error('nomor_surat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tujuan <span class="text-danger">*</span></label>
                    <input type="text" name="tujuan" class="form-control @error('tujuan') is-invalid @enderror" value="{{ old('tujuan') }}" required>
                    @error('tujuan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Perihal <span class="text-danger">*</span></label>
                    <input type="text" name="perihal" class="form-control @error('perihal') is-invalid @enderror" value="{{ old('perihal') }}" required>
                    @error('perihal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Kirim <span class="text-danger">*</span></label>
                    <input type="date" name="tanggal_kirim" class="form-control @error('tanggal_kirim') is-invalid @enderror" value="{{ old('tanggal_kirim', date('Y-m-d')) }}" required>
                    @error('tanggal_kirim')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Lampiran</label>
                    <input type="text" name="lampiran" class="form-control @error('lampiran') is-invalid @enderror" value="{{ old('lampiran') }}" placeholder="Contoh: 2 lembar">
                    @error('lampiran')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">File Draft (PDF/DOC)</label>
                    <input type="file" name="file_draft" class="form-control @error('file_draft') is-invalid @enderror" accept=".pdf,.doc,.docx">
                    @error('file_draft')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small class="text-muted">Maksimal 2MB</small>
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

@push('scripts')
<script>
    document.getElementById('selectSiswa').addEventListener('change', function() {
        const value = this.value;
        if (value) {
            document.getElementsByName('tujuan')[0].value = value;
        }
    });
</script>
@endpush