@extends('layouts.app')

@section('title', 'Rekap Absensi Siswa')

@section('content')
<x-page-header title="Rekap Absensi Siswa">
    Rekap kehadiran siswa per bulan.
    <x-slot:actions>
        @if($kelas && auth()->user()->role !== 'guru')
        <a href="{{ route('ekspor.absensi-siswa', ['kelas_id' => $kelas->id, 'bulan' => $bulan->format('Y-m')]) }}" class="btn btn-outline-secondary me-2">
            <i class="fas fa-file-excel me-1"></i> Ekspor Excel
        </a>
        @endif
        <a href="{{ route('absensi-siswa.index', array_filter(['kelas_id' => $kelas?->id])) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Input Absensi
        </a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <form method="GET" action="{{ route('absensi-siswa.rekap') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold" for="kelas_id">Kelas</label>
                <select name="kelas_id" id="kelas_id" class="form-select">
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" @selected($kelas?->id === $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold" for="bulan">Bulan</label>
                <input type="month" name="bulan" id="bulan" class="form-control" value="{{ $bulan->format('Y-m') }}" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 fw-semibold">{{ $kelas?->nama_kelas ?? 'Tidak ada kelas' }} · {{ $bulan->translatedFormat('F Y') }}</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-stack-sm table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50" class="text-center">No</th>
                        <th>Nama Siswa</th>
                        <th class="text-center">Hadir</th>
                        <th class="text-center">Izin</th>
                        <th class="text-center">Sakit</th>
                        <th class="text-center">Alpha</th>
                        <th class="text-center">Kehadiran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswa as $i => $s)
                    @php($r = $rekap[$s->id])
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td class="fw-semibold">{{ $s->nama_lengkap }}</td>
                        <td class="text-center" data-label="Hadir">{{ $r['hadir'] }}</td>
                        <td class="text-center" data-label="Izin">{{ $r['izin'] }}</td>
                        <td class="text-center" data-label="Sakit">{{ $r['sakit'] }}</td>
                        <td class="text-center" data-label="Alpha">{{ $r['alpha'] }}</td>
                        <td class="text-center" data-label="Kehadiran">{{ $r['persen'] === null ? '—' : $r['persen'].'%' }}</td>
                    </tr>
                    @empty
                    <x-empty-row :colspan="7" icon="fa-user-graduate">Tidak ada siswa aktif untuk direkap.</x-empty-row>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
