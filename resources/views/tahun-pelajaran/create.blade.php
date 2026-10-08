@extends('layouts.app')

@section('title', 'Tambah Tahun Pelajaran')

@section('content')
<x-page-header title="Tambah Tahun Pelajaran" cols="8">
    Masukkan informasi dasar tahun pelajaran baru.
    <x-slot:actions>
        <a href="{{ route('tahun-pelajaran.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('tahun-pelajaran.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label class="form-label">Kode</label>
                <input type="text" name="kode" value="{{ old('kode') }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="nama" value="{{ old('nama') }}" class="form-control" required>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_aktif" id="is_aktif" {{ old('is_aktif') ? 'checked' : '' }}>
                <label class="form-check-label" for="is_aktif">Aktifkan tahun pelajaran ini</label>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
    </div>
</div>
@endsection
