@extends('layouts.app')

@section('title', 'Tambah Sarana')

@section('content')
<div class="mb-4">
    <a href="{{ route('sarana.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
</div>

<div class="card shadow-sm">
    <div class="card-header">
        <h5>Tambah Sarana Prasarana</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('sarana.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Sarana <span class="text-danger">*</span></label>
                    <input type="text" name="kode_sarana" class="form-control @error('kode_sarana') is-invalid @enderror" value="{{ old('kode_sarana') }}" required>
                    @error('kode_sarana')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <small>Contoh: PR-001, LAP-01, dll (unik)</small>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Sarana <span class="text-danger">*</span></label>
                    <input type="text" name="nama_sarana" class="form-control @error('nama_sarana') is-invalid @enderror" value="{{ old('nama_sarana') }}" required>
                    @error('nama_sarana')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategori as $kat)
                        <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                    @error('kategori_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                    <input type="number" name="jumlah" class="form-control @error('jumlah') is-invalid @enderror" value="{{ old('jumlah', 1) }}" min="1" required>
                    @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kondisi</label>
                    <select name="kondisi" class="form-select">
                        <option value="baik" {{ old('kondisi') == 'baik' ? 'selected' : '' }}>Baik</option>
                        <option value="rusak_ringan" {{ old('kondisi') == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                        <option value="rusak_berat" {{ old('kondisi') == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                        <option value="hilang" {{ old('kondisi') == 'hilang' ? 'selected' : '' }}>Hilang</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tahun Pengadaan</label>
                    <input type="number" name="tahun_pengadaan" class="form-control" value="{{ old('tahun_pengadaan') }}" min="1900" max="{{ date('Y')+1 }}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lokasi Ruang</label>
                    <input type="text" name="lokasi_ruang" class="form-control" value="{{ old('lokasi_ruang') }}" placeholder="Contoh: Lab IPA, Ruang Guru, Aula">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Spesifikasi</label>
                    <textarea name="spesifikasi" class="form-control" rows="2">{{ old('spesifikasi') }}</textarea>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Foto</label>
                    <input type="file" name="foto" class="form-control @error('foto') is-invalid @enderror" accept="image/*">
                    @error('foto')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection