@extends('layouts.app')

@section('title', 'Tahun Pelajaran')

@section('content')
<x-page-header title="Tahun Pelajaran">
    Kelola daftar tahun pelajaran untuk sistem rapor dan arsip.
</x-page-header>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="fas fa-plus-circle me-1"></i> Tambah Tahun Pelajaran
    </div>
    <div class="card-body">
        <form action="{{ route('tahun-pelajaran.store') }}" method="POST">
            @csrf
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Kode <span class="text-danger">*</span></label>
                    <input type="text" name="kode" value="{{ old('kode') }}" class="form-control @error('kode') is-invalid @enderror" required>
                    @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="nama" value="{{ old('nama') }}" class="form-control @error('nama') is-invalid @enderror" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="is_aktif" id="is_aktif" {{ old('is_aktif') ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_aktif">Aktifkan tahun ini</label>
                    </div>
                </div>
                <div class="col-md-2 text-md-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Nama</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tahunPelajaran as $item)
                    <tr>
                        <td>{{ $item->kode }}</td>
                        <td>{{ $item->nama }}</td>
                        <td>{{ $item->is_aktif ? 'Aktif' : 'Tidak aktif' }}</td>
                        <td class="text-end">
                            <a href="{{ route('tahun-pelajaran.edit', $item) }}" class="btn btn-sm btn-secondary me-2">Ubah</a>
                            <form action="{{ route('tahun-pelajaran.destroy', $item) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Hapus tahun pelajaran ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $tahunPelajaran->links() }}
</div>
@endsection
