@extends('layouts.app')

@section('title', 'Portal Wali')

@section('content')
<x-page-header title="Portal Wali">
    Pantau kehadiran, nilai, dan jadwal anak Anda.
</x-page-header>

@forelse($anak as $s)
    @php($r = $rekap[$s->id])
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                <div>
                    <h5 class="fw-semibold mb-1">{{ $s->nama_lengkap }}</h5>
                    <div class="text-muted small">NIS {{ $s->nis }} · Kelas {{ $s->kelas?->nama_kelas ?? '—' }}</div>
                </div>
                <a href="{{ route('wali.show', $s) }}" class="btn btn-primary btn-sm">Lihat detail <i class="fas fa-arrow-right ms-1"></i></a>
            </div>
            <hr>
            <div class="small text-muted mb-2">Kehadiran {{ now()->translatedFormat('F Y') }}</div>
            <div class="d-flex flex-wrap gap-2">
                <span class="badge bg-success-subtle text-success-emphasis">Hadir {{ $r['hadir'] }}</span>
                <span class="badge bg-info-subtle text-info-emphasis">Izin {{ $r['izin'] }}</span>
                <span class="badge bg-warning-subtle text-warning-emphasis">Sakit {{ $r['sakit'] }}</span>
                <span class="badge bg-danger-subtle text-danger-emphasis">Alpha {{ $r['alpha'] }}</span>
            </div>
        </div>
    </div>
@empty
    <div class="card shadow-sm border-0">
        <div class="card-body text-center py-5">
            <i class="fas fa-link-slash fa-2x text-muted mb-3"></i>
            <h5 class="fw-semibold">Akun Anda belum ditautkan ke data siswa</h5>
            <p class="text-muted mb-0">Hubungi admin madrasah agar akun ini ditautkan ke data anak Anda.</p>
        </div>
    </div>
@endforelse
@endsection
