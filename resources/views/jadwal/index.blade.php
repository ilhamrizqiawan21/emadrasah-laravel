@extends('layouts.app')

@section('title', 'Jadwal Pelajaran')

@section('content')
<x-page-header title="Jadwal Pelajaran">
    Lihat dan kelola jadwal pelajaran per kelas, atau gunakan mode grid untuk input serentak.
    <x-slot:actions>
        <a href="{{ route('jadwal.grid') }}" class="btn btn-success me-2">
            <i class="fas fa-table-cells me-1"></i> Mode Grid Cepat
        </a>
        <a href="{{ route('jadwal.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Manual
        </a>
    </x-slot:actions>
</x-page-header>

<!-- Filter Kelas: Pilihan Dropdown & Quick Badges -->
<div class="card shadow-sm mb-4 border-0">
    <div class="card-body p-3">
        <form method="GET" action="{{ route('jadwal.index') }}" class="row g-3 align-items-center" id="filterForm">
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fas fa-door-open text-primary"></i></span>
                    <select name="kelas_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Pilih Kelas untuk Melihat Jadwal --</option>
                        @foreach($kelasList as $k)
                            <option value="{{ $k->id }}" {{ $selectedKelas && $selectedKelas->id == $k->id ? 'selected' : '' }}>
                                Kelas {{ $k->nama_kelas }} (Tingkat {{ $k->tingkat }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-7 d-flex flex-wrap align-items-center gap-1">
                <span class="small text-muted me-1 fw-semibold">Pilih Cepat:</span>
                @foreach($kelasList as $k)
                    <a href="{{ route('jadwal.index', ['kelas_id' => $k->id]) }}"
                       class="btn btn-sm {{ $selectedKelas && $selectedKelas->id == $k->id ? 'btn-primary' : 'btn-light border' }} py-1 px-2"
                       style="font-size:0.8rem;">
                        {{ $k->nama_kelas }}
                    </a>
                @endforeach
                @if($selectedKelas)
                    <a href="{{ route('jadwal.index') }}" class="btn btn-sm btn-outline-secondary py-1 px-2 ms-2" title="Reset">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($selectedKelas)
<div class="card border-0 shadow-sm overflow-hidden mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-calendar-week text-primary me-2"></i>Jadwal Kelas {{ $selectedKelas->nama_kelas }}
            </h5>
            <small class="text-muted">Tingkat {{ $selectedKelas->tingkat }} &middot; Tahun Ajaran Aktif</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('jadwal.grid') }}" class="btn btn-sm btn-outline-success">
                <i class="fas fa-edit me-1"></i> Edit di Grid
            </a>
            <a href="{{ route('jadwal.create') }}?kelas_id={{ $selectedKelas->id }}" class="btn btn-sm btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah di Kelas Ini
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="jadwal-container border-0 shadow-none rounded-0">
            <table class="jadwal-table">
                <thead>
                    <tr>
                        <th class="sesi-cell">Sesi &amp; Waktu</th>
                        @foreach($hariList as $hari)
                            <th>{{ $hari }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @php
                        $groupedBySesi = $jamPelajaran->groupBy('sesi_ke')->sortKeys();
                    @endphp
                    @foreach($groupedBySesi as $sesiKe => $jamItems)
                        @php $firstJam = $jamItems->first(); @endphp
                        <tr>
                            <td class="sesi-cell">
                                <strong>Sesi {{ $sesiKe }}</strong>
                                <small>{{ substr($firstJam->jam_mulai,0,5) }} – {{ substr($firstJam->jam_selesai,0,5) }}</small>
                            </td>
                            @foreach($hariList as $hari)
                                @php
                                    $jam = $jamItems->firstWhere('hari', $hari);
                                    $key = $selectedKelas->id . '_' . $hari . '_' . $sesiKe;
                                    $data = $jadwalGrid[$key] ?? null;
                                @endphp
                                <td class="position-relative cell-interactive">
                                    @if($data)
                                        <div class="cell-content">
                                            <span class="guru-kode">{{ $data['guru_kode'] }}</span>
                                            <span class="guru-nama">{{ $data['guru_nama'] }}</span>
                                            <div class="mapel-name">{{ $data['mapel'] }}</div>

                                            {{-- Hover Action Buttons --}}
                                            <div class="cell-actions-hover mt-1">
                                                <a href="{{ route('jadwal.edit', $data['jadwal_id']) }}" class="btn btn-xs btn-outline-primary py-0 px-1" title="Ubah">
                                                    <i class="fas fa-pencil" style="font-size:0.7rem;"></i>
                                                </a>
                                                <button type="button" class="btn btn-xs btn-outline-danger py-0 px-1" title="Hapus"
                                                        onclick="confirmDelete('{{ route('jadwal.destroy', $data['jadwal_id']) }}', {{ Js::from($data['mapel'] . ' (' . $data['guru_nama'] . ')') }})">
                                                    <i class="fas fa-trash" style="font-size:0.7rem;"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        <a href="{{ route('jadwal.create') }}?kelas_id={{ $selectedKelas->id }}&hari={{ $hari }}"
                                           class="empty-cell text-decoration-none d-block py-2" title="Klik untuk tambah jadwal di slot ini">
                                            <span class="empty-plus text-muted opacity-50">+</span>
                                        </a>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@else
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5">
        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center p-4 mb-3" style="width:80px;height:80px;">
            <i class="fas fa-calendar-days fa-2x text-primary"></i>
        </div>
        <h5 class="fw-bold text-dark">Pilih Kelas Terlebih Dahulu</h5>
        <p class="text-muted small mb-3">Pilih salah satu kelas di atas untuk melihat kalender jadwal mingguan, atau buka Mode Grid untuk melihat seluruh kelas sekaligus.</p>
        <a href="{{ route('jadwal.grid') }}" class="btn btn-success px-4">
            <i class="fas fa-table-cells me-1"></i> Buka Mode Grid Jadwal
        </a>
    </div>
</div>
@endif

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<style>
.cell-interactive {
    transition: background-color 0.15s;
}
.cell-interactive:hover {
    background-color: #f8fafc;
}
.cell-actions-hover {
    opacity: 0;
    transition: opacity 0.15s ease-in-out;
}
.cell-interactive:hover .cell-actions-hover {
    opacity: 1;
}
.empty-cell:hover .empty-plus {
    opacity: 1 !important;
    color: var(--em-green-700) !important;
    font-weight: bold;
}
</style>

<script>
function confirmDelete(url, label) {
    if (confirm('Yakin ingin menghapus jadwal ' + label + '?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection