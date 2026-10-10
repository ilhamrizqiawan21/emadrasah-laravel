@extends('layouts.app')

@section('title', 'Kalender Akademik')

@php
    $nav = fn (int $n) => route('kalender.index', ['bulan' => $bulan->copy()->addMonths($n)->format('Y-m')]);
@endphp

@section('content')
<x-page-header title="Kalender Akademik">
    Libur, ujian, kegiatan, dan rapat madrasah.
    <x-slot:actions>
        @if($kelola)<a href="{{ route('kalender.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Tambah Agenda</a>@endif
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-semibold">{{ $bulan->translatedFormat('F Y') }}</h5>
        <div class="d-flex flex-wrap gap-3 align-items-center">
            <span class="small text-muted d-none d-md-inline">
                @foreach(\App\Models\KalenderAkademik::JENIS as [$nama, $warna])<span class="badge bg-{{ $warna }}-subtle text-{{ $warna }}-emphasis me-1">{{ $nama }}</span>@endforeach
            </span>
            <div class="btn-group btn-group-sm">
                <a class="btn btn-outline-secondary" href="{{ $nav(-1) }}" aria-label="Bulan sebelumnya"><i class="fas fa-chevron-left"></i></a>
                <a class="btn btn-outline-secondary" href="{{ route('kalender.index') }}">Hari ini</a>
                <a class="btn btn-outline-secondary" href="{{ $nav(1) }}" aria-label="Bulan berikutnya"><i class="fas fa-chevron-right"></i></a>
            </div>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered mb-0" style="table-layout: fixed; min-width: 640px;">
            <thead class="table-light text-center">
                <tr>@foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $h)<th class="small {{ $h === 'Min' ? 'text-danger' : '' }}">{{ $h }}</th>@endforeach</tr>
            </thead>
            <tbody>
                @foreach($minggu as $pekan)
                    <tr>
                        @foreach($pekan as $hari)
                            @php $ini = $hari['tanggal']->isToday(); @endphp
                            <td class="align-top p-1 {{ $hari['bulanIni'] ? '' : 'bg-light text-muted' }}" style="height: 92px;">
                                <div class="small {{ $ini ? 'fw-bold text-primary' : '' }} {{ $hari['tanggal']->isSunday() && $hari['bulanIni'] ? 'text-danger' : '' }}">{{ $hari['tanggal']->day }}</div>
                                @foreach($hari['agenda']->take(3) as $a)
                                    @php $w = \App\Models\KalenderAkademik::JENIS[$a->jenis][1]; @endphp
                                    <div class="small text-truncate rounded px-1 mb-1 bg-{{ $w }}-subtle text-{{ $w }}-emphasis" title="{{ $a->judul }}">{{ $a->judul }}</div>
                                @endforeach
                                @if($hari['agenda']->count() > 3)<div class="small text-muted">+{{ $hari['agenda']->count() - 3 }} lagi</div>@endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white fw-semibold">Agenda bulan {{ $bulan->translatedFormat('F Y') }}</div>
    <div class="list-group list-group-flush">
        @forelse($daftarBulan as $a)
            @php [$namaJenis, $w] = \App\Models\KalenderAkademik::JENIS[$a->jenis]; @endphp
            <div class="list-group-item d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <span class="badge bg-{{ $w }}-subtle text-{{ $w }}-emphasis me-1">{{ $namaJenis }}</span>
                    <span class="fw-semibold">{{ $a->judul }}</span>
                    <div class="small text-muted">
                        {{ $a->tanggal_mulai->isSameDay($a->tanggal_selesai) ? $a->tanggal_mulai->translatedFormat('l, d F Y') : $a->tanggal_mulai->translatedFormat('d F').' – '.$a->tanggal_selesai->translatedFormat('d F Y') }}
                        @if($a->keterangan) · {{ $a->keterangan }}@endif
                    </div>
                </div>
                @if($kelola)
                    <div class="d-flex gap-2">
                        <a href="{{ route('kalender.edit', $a) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                        <form method="POST" action="{{ route('kalender.destroy', $a) }}" onsubmit="return confirm('Hapus agenda ini?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </div>
                @endif
            </div>
        @empty
            <div class="list-group-item text-muted">Tidak ada agenda pada bulan ini.</div>
        @endforelse
    </div>
</div>
@endsection
