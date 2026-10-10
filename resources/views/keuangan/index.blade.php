@extends('layouts.app')

@section('title', 'Keuangan')

@php
    $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.');
    $warna = ['belum' => 'bg-secondary-subtle text-secondary-emphasis', 'sebagian' => 'bg-warning-subtle text-warning-emphasis', 'lunas' => 'bg-success-subtle text-success-emphasis'];
@endphp

@section('content')
<x-page-header title="Keuangan">
    Tagihan SPP dan biaya lain per siswa, beserta pembayarannya.
    <x-slot:actions>
        <button class="btn btn-primary" type="button" data-bs-toggle="collapse" data-bs-target="#form-generate" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}">
            <i class="fas fa-plus me-1"></i> Buat Tagihan
        </button>
    </x-slot:actions>
</x-page-header>

<div class="collapse {{ $errors->any() ? 'show' : '' }} mb-3" id="form-generate">
    <div class="card shadow-sm border-0">
        <div class="card-body">
            <form method="POST" action="{{ route('keuangan.generate') }}" class="row g-3">
                @csrf
                <div class="col-md-3">
                    <label class="form-label" for="g-jenis">Jenis</label>
                    <select name="jenis" id="g-jenis" class="form-select @error('jenis') is-invalid @enderror">
                        @foreach(\App\Models\Tagihan::JENIS as $j)<option @selected(old('jenis', 'SPP') === $j)>{{ $j }}</option>@endforeach
                    </select>
                    @error('jenis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="g-periode">Bulan (untuk SPP)</label>
                    <input type="month" name="periode" id="g-periode" class="form-control @error('periode') is-invalid @enderror" value="{{ old('periode', now()->format('Y-m')) }}">
                    @error('periode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="g-kelas">Kelas</label>
                    <select name="kelas_id" id="g-kelas" class="form-select">
                        <option value="">Semua siswa aktif</option>
                        @foreach($kelas as $k)<option value="{{ $k->id }}" @selected(old('kelas_id') == $k->id)>{{ $k->nama_kelas }}</option>@endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="g-jumlah">Jumlah per siswa (Rp)</label>
                    <input type="number" name="jumlah" id="g-jumlah" min="1" class="form-control @error('jumlah') is-invalid @enderror" value="{{ old('jumlah') }}" required>
                    @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="g-tempo">Jatuh tempo</label>
                    <input type="date" name="jatuh_tempo" id="g-tempo" class="form-control @error('jatuh_tempo') is-invalid @enderror" value="{{ old('jatuh_tempo', now()->endOfMonth()->toDateString()) }}" required>
                    @error('jatuh_tempo')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="g-ket">Keterangan (opsional)</label>
                    <input type="text" name="keterangan" id="g-ket" maxlength="255" class="form-control" value="{{ old('keterangan') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">Buat Tagihan</button>
                </div>
                <div class="col-12 small text-muted">Siswa yang sudah punya tagihan jenis dan bulan yang sama dilewati, jadi aman dijalankan ulang.</div>
            </form>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    @foreach(['tagihan' => 'Total tagihan', 'terbayar' => 'Terbayar', 'tunggakan' => 'Belum terbayar'] as $k => $label)
        <div class="col-md-4">
            <div class="card shadow-sm border-0"><div class="card-body">
                <div class="small text-muted">{{ $label }}</div>
                <div class="fs-4 fw-semibold">{{ $rp($ringkasan[$k]) }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <form method="GET" action="{{ route('keuangan.index') }}" class="row g-2">
            <div class="col-md-3"><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama atau NIS" aria-label="Cari siswa"></div>
            <div class="col-md-2">
                <select name="status" class="form-select" aria-label="Status">
                    <option value="">Semua status</option>
                    @foreach(['belum' => 'Belum bayar', 'sebagian' => 'Sebagian', 'lunas' => 'Lunas', 'terlambat' => 'Terlambat'] as $v => $l)<option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="kelas_id" class="form-select" aria-label="Kelas">
                    <option value="">Semua kelas</option>
                    @foreach($kelas as $k)<option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama_kelas }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="jenis" class="form-select" aria-label="Jenis">
                    <option value="">Semua jenis</option>
                    @foreach(\App\Models\Tagihan::JENIS as $j)<option @selected(request('jenis') === $j)>{{ $j }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-2"><input type="month" name="periode" value="{{ request('periode') }}" class="form-control" aria-label="Bulan"></div>
            <div class="col-md-1"><button class="btn btn-outline-primary w-100" aria-label="Terapkan filter"><i class="fas fa-search"></i></button></div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-stack-sm align-middle mb-0">
            <thead class="table-light">
                <tr><th>Siswa</th><th>Tagihan</th><th>Jatuh tempo</th><th class="text-end">Jumlah</th><th class="text-end">Sisa</th><th>Status</th><th class="text-end">Aksi</th></tr>
            </thead>
            <tbody>
                @forelse($daftar as $t)
                    <tr>
                        <td data-label="Siswa"><div class="fw-semibold">{{ $t->siswa->nama_lengkap }}</div><div class="small text-muted">{{ $t->siswa->kelas?->nama_kelas }} · NIS {{ $t->siswa->nis }}</div></td>
                        <td data-label="Tagihan">{{ $t->jenis }}@if($t->periode) <span class="text-muted">· {{ $t->namaPeriode() }}</span>@endif</td>
                        <td data-label="Jatuh tempo">{{ $t->jatuh_tempo->translatedFormat('d M Y') }}</td>
                        <td data-label="Jumlah" class="text-end">{{ $rp($t->jumlah) }}</td>
                        <td data-label="Sisa" class="text-end">{{ $rp($t->sisa()) }}</td>
                        <td data-label="Status">
                            <span class="badge {{ $warna[$t->status()] }}">{{ ['belum' => 'Belum bayar', 'sebagian' => 'Sebagian', 'lunas' => 'Lunas'][$t->status()] }}</span>
                            @if($t->terlambat())<span class="badge bg-danger-subtle text-danger-emphasis">Terlambat</span>@endif
                        </td>
                        <td class="text-end"><a href="{{ route('keuangan.tagihan.show', $t) }}" class="btn btn-sm btn-outline-primary">Rincian</a></td>
                    </tr>
                @empty
                    <x-empty-row :colspan="7" icon="fa-wallet">Belum ada tagihan.</x-empty-row>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($daftar->hasPages())
        <div class="card-footer bg-white">{{ $daftar->links() }}</div>
    @endif
</div>
@endsection
