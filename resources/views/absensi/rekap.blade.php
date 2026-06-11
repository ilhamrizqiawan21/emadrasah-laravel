@extends('layouts.app')

@section('title', 'Rekap Absensi Guru')

@section('content')
<div class="container-fluid px-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="fas fa-chart-simple me-2 text-primary"></i> Rekap Absensi Guru
            </h1>
            <p class="text-muted mb-0">Rekapitulasi kehadiran per bulan</p>
        </div>
        <div>
            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                <i class="fas fa-calendar-day me-1"></i> Absensi Harian
            </a>
        </div>
    </div>

    <!-- Filter -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('absensi.rekap') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="fas fa-calendar-alt me-1"></i> Bulan</label>
                    <select name="bulan" class="form-select">
                        @for($m=1; $m<=12; $m++)
                            <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                                {{ date('F', mktime(0,0,0,$m,1)) }}
                            </option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold"><i class="fas fa-calendar-week me-1"></i> Tahun</label>
                    <select name="tahun" class="form-select">
                        @for($y=2023; $y<=date('Y')+1; $y++)
                            <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter me-1"></i> Tampilkan
                    </button>
                </div>
                <div class="col-md-4 text-md-end">
                    <a href="{{ route('absensi.export-pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" target="_blank" class="btn btn-danger">
                        <i class="fas fa-file-pdf me-1"></i> Cetak Rekap PDF
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistik Bulanan -->
    @php
        $totalHadir = 0; $totalIzin = 0; $totalSakit = 0; $totalAlpha = 0;
        foreach ($rekap as $data) {
            $totalHadir += $data['hadir'];
            $totalIzin += $data['izin'];
            $totalSakit += $data['sakit'];
            $totalAlpha += $data['alpha'];
        }
        $totalHari = $totalHadir + $totalIzin + $totalSakit + $totalAlpha;
        $persenHadir = $totalHari > 0 ? round(($totalHadir / $totalHari) * 100) : 0;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Total Kehadiran</span>
                            <h3 class="mb-0 fw-bold">{{ $totalHari }}</h3>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2">
                            <i class="fas fa-calendar-check fs-5 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Persentase Hadir</span>
                            <h3 class="mb-0 fw-bold text-success">{{ $persenHadir }}%</h3>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-2">
                            <i class="fas fa-chart-line fs-5 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Rata-rata Harian</span>
                            <h3 class="mb-0 fw-bold">{{ round($totalHari / 30) }}</h3>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 p-2">
                            <i class="fas fa-users fs-5 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted small">Alpha</span>
                            <h3 class="mb-0 fw-bold text-danger">{{ $totalAlpha }}</h3>
                        </div>
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2">
                            <i class="fas fa-exclamation-triangle fs-5 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Rekap -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-table me-2 text-primary"></i> 
                Detail Rekap Bulan {{ date('F', mktime(0,0,0,$bulan,1)) }} {{ $tahun }}
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Guru</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Izin</th>
                            <th class="text-center">Sakit</th>
                            <th class="text-center">Alpha</th>
                            <th class="text-center">Total</th>
                            <th class="text-center">Kehadiran</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rekap as $data)
                        @php
                            $guru = $data['guru'] ?? null;
                            $total = $data['hadir'] + $data['izin'] + $data['sakit'] + $data['alpha'];
                            $persen = $total > 0 ? round(($data['hadir'] / $total) * 100) : 0;
                            $progressColor = $persen >= 90 ? 'success' : ($persen >= 70 ? 'warning' : 'danger');
                        @endphp
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-primary bg-opacity-10 p-2 me-2">
                                        <i class="fas fa-chalkboard-user text-primary"></i>
                                    </div>
                                    <div>
                                        <span class="fw-semibold">{{ $guru ? $guru->nama : 'Unknown' }}</span>
                                        <br><span class="badge bg-secondary">{{ $guru ? $guru->kode : '-' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-bold text-success">{{ $data['hadir'] }}</td>
                            <td class="text-center text-warning">{{ $data['izin'] }}</td>
                            <td class="text-center text-info">{{ $data['sakit'] }}</td>
                            <td class="text-center text-danger">{{ $data['alpha'] }}</td>
                            <td class="text-center fw-bold">{{ $total }}</td>
                            <td class="text-center">
                                <div class="progress" style="height: 8px;">
                                    <div class="progress-bar bg-{{ $progressColor }}" role="progressbar" style="width: {{ $persen }}%;" aria-valuenow="{{ $persen }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted">{{ $persen }}%</small>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @if(count($rekap) == 0)
        <div class="card-body text-center py-5">
            <i class="fas fa-chart-simple fa-3x text-muted mb-3"></i>
            <p class="text-muted">Belum ada data absensi untuk bulan ini</p>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
</script>
@endpush