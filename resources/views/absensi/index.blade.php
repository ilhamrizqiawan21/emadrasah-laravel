@extends('layouts.app')

@section('title', 'Absensi Guru')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="fas fa-fingerprint me-2 text-primary"></i> Absensi Guru Harian
            </h1>
            <p class="text-muted mb-0">Catat kehadiran guru hari ini atau tanggal yang dipilih</p>
        </div>
        <div class="mt-2 mt-sm-0">
            <a href="{{ route('absensi.rekap') }}" class="btn btn-outline-info me-2">
                <i class="fas fa-chart-line me-1"></i> Rekap Bulanan
            </a>
            <a href="{{ route('absensi.index') }}" class="btn btn-secondary">
                <i class="fas fa-calendar-day me-1"></i> Hari Ini
            </a>
        </div>
    </div>

    <!-- Statistik Ringkasan -->
    @php
        $totalGuru = $gurus->count();
        $hadir = 0; $izin = 0; $sakit = 0; $alpha = 0;
        foreach ($gurus as $guru) {
            $agenda = $agendas[$guru->id] ?? null;
            $status = $agenda ? $agenda->status : 'hadir';
            if ($status == 'hadir') $hadir++;
            elseif ($status == 'izin') $izin++;
            elseif ($status == 'sakit') $sakit++;
            elseif ($status == 'alpha') $alpha++;
        }
        $hadirPersen = $totalGuru > 0 ? round(($hadir / $totalGuru) * 100) : 0;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase small fw-semibold">Total Guru</span>
                            <h2 class="mb-0 mt-1 fw-bold">{{ $totalGuru }}</h2>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="fas fa-chalkboard-user fs-4 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase small fw-semibold">Hadir</span>
                            <h2 class="mb-0 mt-1 fw-bold text-success">{{ $hadir }}</h2>
                            <small class="text-muted">{{ $hadirPersen }}%</small>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="fas fa-check-circle fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase small fw-semibold">Tidak Hadir</span>
                            <h2 class="mb-0 mt-1 fw-bold text-warning">{{ $izin + $sakit + $alpha }}</h2>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="fas fa-user-slash fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm h-100 bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase small fw-semibold">Alpha</span>
                            <h2 class="mb-0 mt-1 fw-bold text-danger">{{ $alpha }}</h2>
                        </div>
                        <div class="rounded-circle bg-danger bg-opacity-10 p-3">
                            <i class="fas fa-times-circle fs-4 text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tanggal -->
    <div class="card shadow-sm mb-4 border-0">
        <div class="card-body">
            <form method="GET" action="{{ route('absensi.index') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold"><i class="fas fa-calendar-alt me-1"></i> Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ $tanggal->format('Y-m-d') }}" max="{{ today()->format('Y-m-d') }}" required>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i> Tampilkan
                    </button>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="badge bg-secondary p-2 text-wrap text-start" style="line-height:1.4;">
                        <i class="fas fa-info-circle me-1"></i> Status "Tidak Hadir" membutuhkan penunjukan guru pengganti
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- Form Absensi -->
    <form method="POST" action="{{ route('absensi.store') }}" id="formAbsensi">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $tanggal instanceof \Carbon\Carbon ? $tanggal->format('Y-m-d') : \Carbon\Carbon::parse($tanggal)->format('Y-m-d') }}">

        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="fas fa-calendar-check me-2 text-primary"></i> 
                    Absensi Tanggal: <span class="text-primary">{{ $tanggal->translatedFormat('d F Y') }}</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table data-hide-sm="1 2" class="table table-stack-sm table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="text-center">No</th>
                                <th>Kode</th>
                                <th>Nama Guru</th>
                                <th width="160" class="text-center">Status</th>
                                <th>Keterangan</th>
                                <th width="220" class="text-center">Guru Pengganti</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gurus as $index => $guru)
                            @php
                                $agenda = $agendas[$guru->id] ?? null;
                                $status = $agenda ? $agenda->status : 'hadir';
                                $keterangan = $agenda ? $agenda->keterangan : '';
                                $pengganti = $agenda ? $agenda->guruPengganti->first() : null;
                            @endphp
                            <tr class="absensi-row">
                                <td class="text-center">{{ $index + 1 }}</td>
                                <td><span class="badge bg-secondary">{{ $guru->kode }}</span></td>
                                <td class="fw-semibold">{{ $guru->nama }}</td>
                                <td data-label="Status">
                                    <div class="status-field" data-status="{{ $status }}">
                                        <i class="fas status-field__icon" aria-hidden="true"></i>
                                        <select name="status[{{ $guru->id }}]" class="form-select form-select-sm status-select" data-guru-id="{{ $guru->id }}" aria-label="Status {{ $guru->nama }}">
                                            <option value="hadir" {{ $status=='hadir' ? 'selected' : '' }} data-bg="success">Hadir</option>
                                            <option value="izin" {{ $status=='izin' ? 'selected' : '' }} data-bg="warning">Izin</option>
                                            <option value="sakit" {{ $status=='sakit' ? 'selected' : '' }} data-bg="info">Sakit</option>
                                            <option value="alpha" {{ $status=='alpha' ? 'selected' : '' }} data-bg="danger">Alpha</option>
                                        </select>
                                    </div>
                                </td>
                                <td data-label="Keterangan">
                                    <input type="text" name="keterangan[{{ $guru->id }}]" class="form-control form-control-sm" value="{{ old("keterangan.$guru->id", $keterangan) }}" placeholder="Opsional">
                                </td>
                                <td data-label="Guru Pengganti" class="text-center">
                                    @if($status != 'hadir')
                                        @if($agenda && $agenda->id)
                                            <a href="{{ route('absensi.pengganti', $agenda->id) }}" class="btn btn-sm btn-info mb-1" data-bs-toggle="tooltip" title="Tunjuk guru pengganti">
                                                <i class="fas fa-user-friends me-1"></i> Tunjuk Pengganti
                                            </a>
                                            @if($pengganti)
                                                <span class="badge bg-success d-block mt-1">
                                                    <i class="fas fa-user-check me-1"></i> {{ $pengganti->guruPengganti->nama ?? '-' }}
                                                </span>
                                            @endif
                                        @else
                                            <span class="text-muted small"><i class="fas fa-save me-1"></i> Simpan dulu</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white text-end py-3">
                <button type="submit" class="btn btn-primary px-4" id="btnSimpan">
                    <i class="fas fa-save me-2"></i> Simpan Absensi
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    // Submit dengan loading state
    document.getElementById('formAbsensi')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('btnSimpan');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Menyimpan...';
    });

    // Ikon dan warna status mengikuti pilihan (ikon Font Awesome, bukan emoji)
    document.querySelectorAll('.status-field select').forEach(function (select) {
        select.addEventListener('change', function () {
            select.closest('.status-field').dataset.status = select.value;
        });
    });

    // Tooltips
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
    });
</script>
@endpush