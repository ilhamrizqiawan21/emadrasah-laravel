@extends('layouts.app')

@section('title', $pengumuman->exists ? 'Ubah Pengumuman' : 'Buat Pengumuman')

@section('content')
<x-page-header :title="$pengumuman->exists ? 'Ubah Pengumuman' : 'Buat Pengumuman'">
    <x-slot:actions>
        <a href="{{ route('pengumuman.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="row"><div class="col-lg-8">
    <div class="card shadow-sm border-0"><div class="card-body">
        <form method="POST" action="{{ $pengumuman->exists ? route('pengumuman.update', $pengumuman) : route('pengumuman.store') }}">
            @csrf
            @if($pengumuman->exists) @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label" for="judul">Judul <span class="text-danger">*</span></label>
                <input type="text" name="judul" id="judul" maxlength="150" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $pengumuman->judul) }}" required>
                @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="isi">Isi <span class="text-danger">*</span></label>
                <textarea name="isi" id="isi" rows="6" maxlength="5000" class="form-control @error('isi') is-invalid @enderror" required>{{ old('isi', $pengumuman->isi) }}</textarea>
                @error('isi')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="target">Ditujukan kepada</label>
                    <select name="target" id="target" class="form-select @error('target') is-invalid @enderror">
                        @foreach(\App\Models\Pengumuman::TARGET as $k => $n)<option value="{{ $k }}" @selected(old('target', $pengumuman->target) === $k)>{{ $n }}</option>@endforeach
                    </select>
                    @error('target')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="terbit_pada">Terbit</label>
                    <input type="date" name="terbit_pada" id="terbit_pada" class="form-control @error('terbit_pada') is-invalid @enderror" value="{{ old('terbit_pada', $pengumuman->terbit_pada?->toDateString()) }}" required>
                    @error('terbit_pada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label" for="berakhir_pada">Berakhir (opsional)</label>
                    <input type="date" name="berakhir_pada" id="berakhir_pada" class="form-control @error('berakhir_pada') is-invalid @enderror" value="{{ old('berakhir_pada', $pengumuman->berakhir_pada?->toDateString()) }}">
                    @error('berakhir_pada')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="disematkan" value="1" id="disematkan" @checked(old('disematkan', $pengumuman->disematkan))>
                <label class="form-check-label" for="disematkan">Sematkan di bagian atas</label>
            </div>
            @unless($pengumuman->exists)<p class="small text-muted">Penerima mendapat notifikasi di lonceng saat pengumuman terbit. Pengumuman berjadwal tidak memicu notifikasi.</p>@endunless
            <div class="text-end"><button class="btn btn-primary">{{ $pengumuman->exists ? 'Simpan' : 'Terbitkan' }}</button></div>
        </form>
    </div></div>
</div></div>
@endsection
