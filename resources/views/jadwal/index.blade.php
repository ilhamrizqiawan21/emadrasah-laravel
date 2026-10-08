@extends('layouts.app')

@section('title', 'Jadwal Pelajaran')

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