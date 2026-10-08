@extends('layouts.app')

@section('title', 'Tambah Guru')

@section('content')
<x-page-header title="Tambah Guru Baru">
    <x-slot:actions>
        <a href="{{ route('guru.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('guru.store') }}">
            @csrf

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Kode Guru <span class="text-danger">*</span></label>
                    <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" value="{{ old('kode') }}" required>
                    @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Contoh: 1, 4A, 3/15A (unik)</div>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip') }}">
                    @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label">Bidang Studi</label>
                    <select name="bidang_studi" class="form-select @error('bidang_studi') is-invalid @enderror">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->nama_mapel }}" {{ old('bidang_studi') == $mapel->nama_mapel ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                    @error('bidang_studi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Bidang studi guru (opsional, untuk rekomendasi mapel)</div>
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