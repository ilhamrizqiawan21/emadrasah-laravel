@extends('layouts.app')

@section('title', 'Ubah Tahun Pelajaran')

@section('content')
<div class="row mb-4 align-items-center">
    <div class="col-md-8">
        <h2 class="em-page-title">Ubah Tahun Pelajaran</h2>
        <p class="text-muted">Perbarui data tahun pelajaran.</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="{{ route('tahun-pelajaran.index') }}" class="btn btn-light border">Kembali</a>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form action="{{ route('tahun-pelajaran.update', $tahunPelajaran) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Kode</label>
                <input type="text" name="kode" value="{{ old('kode', $tahunPelajaran->kode) }}" class="form-control" required>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama</label>
                <input type="text" name="nama" value="{{ old('nama', $tahunPelajaran->nama) }}" class="form-control" required>
            </div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="is_aktif" id="is_aktif" {{ old('is_aktif', $tahunPelajaran->is_aktif) ? 'checked' : '' }}>
                <label class="form-check-label" for="is_aktif">Aktifkan tahun pelajaran ini</label>
            </div>

            <button type="submit" class="btn btn-primary">Perbarui</button>
        </form>
    </div>
</div>
@endsection
