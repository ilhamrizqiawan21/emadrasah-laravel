@extends('layouts.app')

@section('title', 'Ajukan Izin')

@section('content')
<x-page-header title="Ajukan Izin / Cuti">
    <x-slot:actions>
        <a href="{{ route('izin-guru.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="row">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <form method="POST" action="{{ route('izin-guru.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="jenis">Jenis <span class="text-danger">*</span></label>
                        <select name="jenis" id="jenis" class="form-select @error('jenis') is-invalid @enderror" required>
                            @foreach(\App\Models\IzinGuru::JENIS as $kode => $nama)
                                <option value="{{ $kode }}" @selected(old('jenis') === $kode)>{{ $nama }}</option>
                            @endforeach
                        </select>
                        @error('jenis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tanggal_mulai">Mulai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', today()->toDateString()) }}" required>
                            @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="tanggal_selesai">Selesai <span class="text-danger">*</span></label>
                            <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai', today()->toDateString()) }}" required>
                            @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="alasan">Alasan <span class="text-danger">*</span></label>
                        <textarea name="alasan" id="alasan" rows="3" maxlength="500" class="form-control @error('alasan') is-invalid @enderror" required>{{ old('alasan') }}</textarea>
                        @error('alasan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <p class="small text-muted">Maksimal {{ \App\Models\IzinGuru::MAKS_HARI }} hari per pengajuan. Hari Minggu tidak dihitung.</p>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Kirim Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
