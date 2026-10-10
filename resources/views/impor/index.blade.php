@extends('layouts.app')

@section('title', 'Impor Data '.$label)

@section('content')
<x-page-header title="Impor Data {{ $label }}">
    Masukkan banyak data sekaligus dari berkas Excel (xlsx) atau CSV.
    <x-slot:actions>
        <a href="{{ route($jenis === 'siswa' ? 'siswa.index' : 'guru.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><span class="badge bg-primary me-2">1</span>Unduh template</h5>
                <p class="text-muted">Isi data pada template ini. Lembar "Petunjuk" di dalamnya menjelaskan format tiap kolom.</p>
                <a href="{{ route('impor.template', $jenis) }}" class="btn btn-outline-primary"><i class="fas fa-file-excel me-2"></i>Unduh template-{{ $jenis }}.xlsx</a>
                <p class="small text-muted mt-3 mb-1">Kolom di template:</p>
                <p class="small mb-0">
                    @foreach($kolom as $k)
                        <span class="badge {{ ($k['wajib'] ?? false) ? 'bg-primary' : 'bg-light text-dark border' }}">{{ $k['label'] }}{{ ($k['wajib'] ?? false) ? ' *' : '' }}</span>
                    @endforeach
                </p>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
                <h5 class="fw-bold mb-3"><span class="badge bg-primary me-2">2</span>Unggah berkas</h5>
                <form method="POST" action="{{ route('impor.proses', $jenis) }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="berkas" class="form-label fw-semibold">Berkas (xlsx atau csv, maks. 2 MB)</label>
                        <input type="file" name="berkas" id="berkas" class="form-control @error('berkas') is-invalid @enderror" accept=".xlsx,.csv" required>
                        @error('berkas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="periksa" value="1" id="periksa" checked>
                        <label class="form-check-label" for="periksa">Hanya periksa, jangan simpan dulu <span class="text-muted">(disarankan untuk percobaan pertama)</span></label>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-upload me-2"></i>Proses berkas</button>
                </form>
            </div>
        </div>
    </div>
</div>

@if($hasil)
<div class="card shadow-sm border-0 mt-4">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold">
            @if($hasil['periksa'])
                <i class="fas fa-magnifying-glass me-2 text-info"></i>Hasil pemeriksaan (belum ada yang disimpan)
            @else
                <i class="fas fa-circle-check me-2 text-success"></i>Hasil impor
            @endif
        </h5>
    </div>
    <div class="card-body">
        <p class="mb-2">
            <span class="badge bg-success">{{ $hasil['diimpor'] }}</span> baris {{ $hasil['periksa'] ? 'siap diimpor' : 'berhasil diimpor' }},
            <span class="badge bg-{{ count($hasil['dilewati']) ? 'warning text-dark' : 'secondary' }}">{{ count($hasil['dilewati']) }}</span> baris dilewati.
        </p>
        @if($hasil['periksa'] && $hasil['diimpor'] > 0)
            <p class="text-muted small mb-0">Jika sudah sesuai, unggah berkas yang sama lagi dengan kotak "Hanya periksa" dikosongkan.</p>
        @endif
    </div>
    @if(count($hasil['dilewati']))
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="table-light"><tr><th width="100">Baris</th><th>Alasan dilewati</th></tr></thead>
            <tbody>
                @foreach(array_slice($hasil['dilewati'], 0, 200) as $d)
                <tr><td>{{ $d['baris'] }}</td><td>{{ $d['pesan'] }}</td></tr>
                @endforeach
            </tbody>
        </table>
        @if(count($hasil['dilewati']) > 200)
            <p class="small text-muted p-3 mb-0">Menampilkan 200 dari {{ count($hasil['dilewati']) }} baris yang dilewati.</p>
        @endif
    </div>
    @endif
</div>
@endif
@endsection
