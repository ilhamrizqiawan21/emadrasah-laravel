@extends('layouts.app')

@section('title', 'Rincian Tagihan')

@php $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.'); @endphp

@section('content')
<x-page-header title="Rincian Tagihan">
    {{ $tagihan->siswa->nama_lengkap }} · {{ $tagihan->siswa->kelas?->nama_kelas }} · NIS {{ $tagihan->siswa->nis }}
    <x-slot:actions>
        <a href="{{ route('keuangan.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="row g-3">
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-3">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Tagihan</dt><dd class="col-sm-8">{{ $tagihan->jenis }}@if($tagihan->periode) · {{ $tagihan->namaPeriode() }}@endif</dd>
                    <dt class="col-sm-4">Jatuh tempo</dt><dd class="col-sm-8">{{ $tagihan->jatuh_tempo->translatedFormat('d F Y') }}@if($tagihan->terlambat()) <span class="badge bg-danger-subtle text-danger-emphasis">Terlambat</span>@endif</dd>
                    <dt class="col-sm-4">Jumlah</dt><dd class="col-sm-8">{{ $rp($tagihan->jumlah) }}</dd>
                    <dt class="col-sm-4">Terbayar</dt><dd class="col-sm-8">{{ $rp($tagihan->totalTerbayar()) }}</dd>
                    <dt class="col-sm-4">Sisa</dt><dd class="col-sm-8 fw-semibold">{{ $rp($tagihan->sisa()) }}</dd>
                    @if($tagihan->keterangan)<dt class="col-sm-4">Keterangan</dt><dd class="col-sm-8">{{ $tagihan->keterangan }}</dd>@endif
                </dl>
            </div>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white fw-semibold">Riwayat pembayaran</div>
            <div class="table-responsive">
                <table class="table table-stack-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Tanggal</th><th>Metode</th><th class="text-end">Jumlah</th><th>Catatan</th><th class="text-end">Aksi</th></tr></thead>
                    <tbody>
                        @forelse($tagihan->pembayaran as $p)
                            <tr>
                                <td data-label="Tanggal">{{ $p->tanggal->translatedFormat('d M Y') }}</td>
                                <td data-label="Metode">{{ \App\Models\Pembayaran::METODE[$p->metode] ?? $p->metode }}</td>
                                <td data-label="Jumlah" class="text-end">{{ $rp($p->jumlah) }}</td>
                                <td data-label="Catatan">{{ $p->catatan }}<div class="small text-muted">{{ $p->pencatat?->name }}</div></td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('keuangan.pembayaran.destroy', $p) }}" onsubmit="return confirm('Batalkan pembayaran ini?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">Batalkan</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <x-empty-row :colspan="5" icon="fa-receipt">Belum ada pembayaran.</x-empty-row>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        @if($tagihan->sisa() > 0)
            <div class="card shadow-sm border-0 mb-3">
                <div class="card-header bg-white fw-semibold">Catat pembayaran</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('keuangan.pembayaran.store', $tagihan) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="jumlah">Jumlah (Rp)</label>
                            <input type="number" name="jumlah" id="jumlah" min="1" max="{{ $tagihan->sisa() }}" class="form-control @error('jumlah') is-invalid @enderror" value="{{ old('jumlah', $tagihan->sisa()) }}" required>
                            @error('jumlah')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label" for="tanggal">Tanggal</label>
                                <input type="date" name="tanggal" id="tanggal" max="{{ today()->toDateString() }}" class="form-control @error('tanggal') is-invalid @enderror" value="{{ old('tanggal', today()->toDateString()) }}" required>
                                @error('tanggal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label" for="metode">Metode</label>
                                <select name="metode" id="metode" class="form-select">
                                    @foreach(\App\Models\Pembayaran::METODE as $k => $n)<option value="{{ $k }}" @selected(old('metode') === $k)>{{ $n }}</option>@endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="catatan">Catatan (opsional)</label>
                            <input type="text" name="catatan" id="catatan" maxlength="255" class="form-control" value="{{ old('catatan') }}">
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-check me-1"></i> Simpan Pembayaran</button>
                    </form>
                </div>
            </div>
        @endif

        @if($tagihan->pembayaran->isEmpty())
            <form method="POST" action="{{ route('keuangan.tagihan.destroy', $tagihan) }}" onsubmit="return confirm('Hapus tagihan ini?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger w-100">Hapus tagihan</button>
            </form>
        @endif
    </div>
</div>
@endsection
