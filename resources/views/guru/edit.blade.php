@extends('layouts.app')

@section('title', 'Edit Guru')

@section('content')
<x-page-header :title="'Edit Guru: '.($guru->nama)">
    <x-slot:actions>
        <a href="{{ route('guru.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('guru.update', $guru) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Guru <span class="text-danger">*</span></label>
                            <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" value="{{ old('kode', $guru->kode) }}" required>
                            @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $guru->nama) }}" required>
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">NIP</label>
                            <input type="text" name="nip" class="form-control @error('nip', $guru->nip) is-invalid @enderror" value="{{ old('nip', $guru->nip) }}">
                            @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control @error('email', $guru->email) is-invalid @enderror" value="{{ old('email', $guru->email) }}">
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="phone" class="form-control @error('phone', $guru->phone) is-invalid @enderror" value="{{ old('phone', $guru->phone) }}">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Bidang Studi</label>
                            <select name="bidang_studi" class="form-select @error('bidang_studi') is-invalid @enderror">
                                <option value="">-- Pilih Mata Pelajaran --</option>
                                @foreach($mapels as $mapel)
                                    <option value="{{ $mapel->nama_mapel }}" {{ old('bidang_studi', $guru->bidang_studi) == $mapel->nama_mapel ? 'selected' : '' }}>
                                        {{ $mapel->nama_mapel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('bidang_studi')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
    </div>
    <div class="col-md-4">
        <div class="card bg-danger bg-opacity-10 border-danger">
            <div class="card-body">
                <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Zona Berbahaya</h6>
                <p class="small">Menghapus guru akan menghapus semua jadwal dan riwayat absensi terkait.</p>
                <button class="btn btn-danger btn-sm" onclick="confirmDelete('{{ route('guru.destroy', $guru) }}', {{ Js::from($guru->nama) }})">
                    <i class="fas fa-trash"></i> Hapus Guru Ini
                </button>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
<script>
function confirmDelete(url, name) {
    if (confirm('Yakin ingin menghapus guru "' + name + '"?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection