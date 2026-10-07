@extends('layouts.app')

@section('title', 'Jadwal Pelajaran')

@push('styles')
<style>
    .jadwal-container {
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
    }
    .jadwal-table {
        min-width: 800px;
        width: 100%;
        border-collapse: collapse;
        font-size: 0.85rem;
    }
    .jadwal-table th,
    .jadwal-table td {
        border: 1px solid #e2e8f0;
        padding: 10px 8px;
        vertical-align: middle;
        text-align: center;
    }
    .jadwal-table th {
        background: #1a6b4a;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
        position: sticky;
        top: 0;
    }
    .jadwal-table thead th.sesi-cell { background: #1a6b4a; color: #fff; }
    .jadwal-table .sesi-cell {
        background: #f0fdf4;
        font-weight: 600;
        width: 110px;
        text-align: left;
        padding-left: 12px;
        border-right: 2px solid #c6e8d7;
    }
    .jadwal-table .sesi-cell strong {
        color: #1a6b4a;
        display: block;
    }
    .jadwal-table .sesi-cell small {
        font-weight: normal;
        color: #4b5563;
        font-size: .75rem;
    }
    .cell-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 4px;
    }
    .guru-kode {
        font-weight: 700;
        font-size: 0.85rem;
        background: #e6f7ec;
        padding: 2px 8px;
        border-radius: 20px;
        display: inline-block;
        color: #1a6b4a;
    }
    .guru-nama {
        font-size: .75rem;
        font-weight: 500;
        color: #1f2937;
    }
    .mapel-name {
        font-size: .75rem;
        color: #6b7280;
        margin-top: 2px;
    }
    .empty-cell {
        color: #9ca3af;
        font-style: italic;
        font-size: 0.75rem;
    }
    /* Warna bergantian untuk baris sesi */
    .jadwal-table tbody tr:nth-child(even) .sesi-cell {
        background: #f8fafc;
    }
    .jadwal-table tbody tr:hover td {
        background: #fefce8;
        transition: background 0.2s;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3">Jadwal Pelajaran</h1>
    <div class="d-flex gap-2">
        <a href="{{ route('jadwal.grid') }}" class="btn btn-success"><i class="fas fa-th"></i> Input Grid</a>
        <a href="{{ route('jadwal.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Manual</a>
    </div>
</div>

<!-- Filter Kelas -->
<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <form method="GET" action="{{ route('jadwal.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label fw-semibold"><i class="fas fa-door-open me-1"></i> Pilih Kelas</label>
                <select name="kelas_id" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" {{ $selectedKelas && $selectedKelas->id == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <a href="{{ route('jadwal.index') }}" class="btn btn-secondary w-100">Reset</a>
            </div>
        </form>
    </div>
</div>

@if($selectedKelas)
<div class="jadwal-container">
    <table class="jadwal-table">
        <thead>
            <tr>
                <th class="sesi-cell">Sesi & Waktu</th>
                @foreach($hariList as $hari)
                    <th>{{ $hari }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @php
                // Kelompokkan jam pelajaran per sesi (berdasarkan sesi_ke, abaikan hari)
                $groupedBySesi = $jamPelajaran->groupBy('sesi_ke')->sortKeys();
            @endphp
            @foreach($groupedBySesi as $sesiKe => $jamItems)
                @php
                    $firstJam = $jamItems->first();
                @endphp
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
                        <td>
                            @if($data)
                                <div class="cell-content">
                                    <span class="guru-kode">{{ $data['guru_kode'] }}</span>
                                    <span class="guru-nama">{{ $data['guru_nama'] }}</span>
                                    <div class="mapel-name">{{ $data['mapel'] }}</div>
                                </div>
                            @else
                                <div class="empty-cell">—</div>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="card shadow-sm border-0">
    <div class="card-body text-center py-5">
        <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
        <p class="text-muted">Silakan pilih kelas terlebih dahulu untuk melihat jadwal.</p>
    </div>
</div>
@endif
@endsection