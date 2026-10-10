@extends('layouts.app')

@section('title', $siswa->nama_lengkap)

@section('content')
<x-page-header :title="$siswa->nama_lengkap">
    NIS {{ $siswa->nis }} · Kelas {{ $siswa->kelas?->nama_kelas ?? '—' }}
    <x-slot:actions>
        <a href="{{ route('wali.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

{{-- Kehadiran --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0 fw-semibold">Kehadiran · {{ $bulan->translatedFormat('F Y') }}</h5>
        <div class="btn-group btn-group-sm">
            <a class="btn btn-outline-secondary" href="{{ route('wali.show', [$siswa, 'bulan' => $bulan->copy()->subMonth()->format('Y-m')]) }}" aria-label="Bulan sebelumnya"><i class="fas fa-chevron-left"></i></a>
            <a class="btn btn-outline-secondary" href="{{ route('wali.show', [$siswa, 'bulan' => $bulan->copy()->addMonth()->format('Y-m')]) }}" aria-label="Bulan berikutnya"><i class="fas fa-chevron-right"></i></a>
        </div>
    </div>
    <div class="card-body">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-success-subtle text-success-emphasis">Hadir {{ $rekap['hadir'] }}</span>
            <span class="badge bg-info-subtle text-info-emphasis">Izin {{ $rekap['izin'] }}</span>
            <span class="badge bg-warning-subtle text-warning-emphasis">Sakit {{ $rekap['sakit'] }}</span>
            <span class="badge bg-danger-subtle text-danger-emphasis">Alpha {{ $rekap['alpha'] }}</span>
        </div>
        @if($tidakHadir->isEmpty())
            <p class="text-muted mb-0">Tidak ada catatan izin, sakit, atau alpha pada bulan ini.</p>
        @else
            <ul class="list-unstyled mb-0">
                @foreach($tidakHadir as $a)
                    <li class="py-1 border-bottom">
                        <span class="fw-semibold">{{ $a->tanggal->translatedFormat('l, d F Y') }}</span>
                        · {{ ucfirst($a->status) }}
                        @if($a->keterangan)<span class="text-muted">— {{ $a->keterangan }}</span>@endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>

{{-- Tagihan --}}
@php $rp = fn ($n) => 'Rp '.number_format($n, 0, ',', '.'); @endphp
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-semibold">Tagihan</h5></div>
    <div class="card-body">
        @forelse($tagihan as $t)
            <div class="py-2 {{ ! $loop->last ? 'border-bottom' : '' }}">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <div>
                        <span class="fw-semibold">{{ $t->jenis }}@if($t->periode) · {{ $t->namaPeriode() }}@endif</span>
                        <div class="small text-muted">Jatuh tempo {{ $t->jatuh_tempo->translatedFormat('d F Y') }}</div>
                    </div>
                    <div class="text-end">
                        <div>{{ $rp($t->jumlah) }}
                            @if($t->status() === 'lunas')<span class="badge bg-success-subtle text-success-emphasis">Lunas</span>
                            @elseif($t->terlambat())<span class="badge bg-danger-subtle text-danger-emphasis">Terlambat</span>
                            @else<span class="badge bg-warning-subtle text-warning-emphasis">Belum lunas</span>@endif
                        </div>
                        @if($t->status() !== 'lunas')<div class="small text-muted">Sisa {{ $rp($t->sisa()) }}</div>@endif
                    </div>
                </div>
                @foreach($t->pembayaran as $p)
                    <div class="small text-muted mt-1">
                        Dibayar {{ $rp($p->jumlah) }} pada {{ $p->tanggal->translatedFormat('d M Y') }}
                        · <a href="{{ route('wali.kuitansi', [$siswa, $p]) }}" target="_blank" rel="noopener">Kuitansi</a>
                    </div>
                @endforeach
            </div>
        @empty
            <p class="text-muted mb-0">Belum ada tagihan.</p>
        @endforelse
    </div>
</div>

{{-- Nilai --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-semibold">Nilai Raport</h5></div>
    <div class="card-body">
        @forelse($nilai as $baris)
            @php($pertama = $baris->first())
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
                <h6 class="fw-semibold mb-0">{{ $pertama->tahunPelajaran?->kode }} · Semester {{ $pertama->semester }}</h6>
                <a href="{{ route('wali.raport', [$siswa, 'tahun_pelajaran_id' => $pertama->tahun_pelajaran_id, 'semester' => $pertama->semester]) }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
                    <i class="fas fa-file-pdf me-1"></i> Unduh Raport
                </a>
            </div>
            <div class="table-responsive mb-4">
                <table class="table table-stack-sm align-middle mb-0">
                    <thead class="table-light"><tr><th>Mata Pelajaran</th><th class="text-end">Nilai Akhir</th><th class="text-end">KKTP</th></tr></thead>
                    <tbody>
                        @foreach($baris as $n)
                            <tr>
                                <td data-label="Mata Pelajaran">{{ $n->mapel?->nama_mapel }}</td>
                                <td data-label="Nilai Akhir" class="text-end fw-semibold">{{ (float) $n->nilai_akhir + 0 }}</td>
                                <td data-label="KKTP" class="text-end">{{ $n->kktp }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @empty
            <p class="text-muted mb-0">Raport belum dirilis oleh madrasah.</p>
        @endforelse
    </div>
</div>

{{-- Jadwal --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-semibold">Jadwal Pelajaran</h5></div>
    <div class="card-body">
        @forelse($jadwal as $hari => $baris)
            <h6 class="fw-semibold mt-2">{{ $hari }}</h6>
            <ul class="list-unstyled mb-3">
                @foreach($baris as $j)
                    <li class="py-1">
                        <span class="text-muted">{{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai, 0, 5) }}</span>
                        · {{ $j->mapel?->nama_mapel }}
                        @if($j->guru)<span class="text-muted">· {{ $j->guru->nama }}</span>@endif
                    </li>
                @endforeach
            </ul>
        @empty
            <p class="text-muted mb-0">Jadwal kelas belum tersedia.</p>
        @endforelse
    </div>
</div>
@endsection
