@extends('layouts.app')

@section('title', $agenda->exists ? 'Ubah Agenda' : 'Tambah Agenda')

@section('content')
<x-page-header :title="$agenda->exists ? 'Ubah Agenda' : 'Tambah Agenda'">
    <x-slot:actions>
        <a href="{{ route('kalender.index', array_filter(['bulan' => $agenda->tanggal_mulai?->format('Y-m')])) }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="row"><div class="col-lg-7">
    <div class="card shadow-sm border-0"><div class="card-body">
        <form method="POST" action="{{ $agenda->exists ? route('kalender.update', $agenda) : route('kalender.store') }}">
            @csrf
            @if($agenda->exists) @method('PUT') @endif
            <div class="mb-3">
                <label class="form-label" for="judul">Judul <span class="text-danger">*</span></label>
                <input type="text" name="judul" id="judul" maxlength="150" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul', $agenda->judul) }}" required>
                @error('judul')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label" for="jenis">Jenis</label>
                <select name="jenis" id="jenis" class="form-select @error('jenis') is-invalid @enderror">
                    @foreach(\App\Models\KalenderAkademik::JENIS as $k => [$n])<option value="{{ $k }}" @selected(old('jenis', $agenda->jenis) === $k)>{{ $n }}</option>@endforeach
                </select>
                @error('jenis')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="tanggal_mulai">Mulai</label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control @error('tanggal_mulai') is-invalid @enderror" value="{{ old('tanggal_mulai', $agenda->tanggal_mulai?->toDateString()) }}" required>
                    @error('tanggal_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label" for="tanggal_selesai">Selesai</label>
                    <input type="date" name="tanggal_selesai" id="tanggal_selesai" class="form-control @error('tanggal_selesai') is-invalid @enderror" value="{{ old('tanggal_selesai', $agenda->tanggal_selesai?->toDateString()) }}" required>
                    @error('tanggal_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label" for="keterangan">Keterangan (opsional)</label>
                <input type="text" name="keterangan" id="keterangan" maxlength="255" class="form-control" value="{{ old('keterangan', $agenda->keterangan) }}">
            </div>
            <p class="small text-muted">Agenda berjenis Libur menghentikan pengingat absensi harian di dashboard pada tanggal tersebut.</p>
            <div class="text-end"><button class="btn btn-primary">Simpan</button></div>
        </form>
    </div></div>
</div></div>
@endsection
