@extends('layouts.app')

@section('title', 'Edit Kategori Sarana')

@section('content')
<x-page-header :title="'Edit Kategori: '.($kategori_sarana->nama_kategori)">
    <x-slot:actions>
        <a href="{{ route('kategori-sarana.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="{{ route('kategori-sarana.update', $kategori_sarana->id) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Nama Kategori <span class="text-danger">*</span></label>
                <input type="text" name="nama_kategori" class="form-control @error('nama_kategori') is-invalid @enderror" value="{{ old('nama_kategori', $kategori_sarana->nama_kategori) }}" required>
                @error('nama_kategori')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="text-end">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
            </div>
        </form>
    </div>
</div>
@endsection