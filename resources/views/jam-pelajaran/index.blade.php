@extends('layouts.app')

@section('title', 'Jam Pelajaran')

@section('content')
<div class="container-fluid px-0">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold">
                <i class="fas fa-clock me-2 text-primary"></i> Jam Pelajaran
            </h1>
            <p class="text-muted mb-0">Kelola sesi dan waktu pelajaran per hari</p>
        </div>
        <div>
            <a href="{{ route('jam-pelajaran.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Tambah Jam
            </a>
        </div>
    </div>

    <!-- Statistik Ringkasan -->
    @php
        $totalSesi = $jamPelajaran->count();
        $hariCount = $jamPelajaran->groupBy('hari')->map->count();
        $senin = $hariCount['Senin'] ?? 0;
        $selasa = $hariCount['Selasa'] ?? 0;
        $rabu = $hariCount['Rabu'] ?? 0;
        $kamis = $hariCount['Kamis'] ?? 0;
        $jumat = $hariCount['Jumat'] ?? 0;
    @endphp

    <div class="row g-3 mb-4">
        <div class="col-md-3 col-6">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted text-uppercase small fw-semibold">Total Sesi</span>
                            <h2 class="mb-0 mt-1 fw-bold">{{ $totalSesi }}</h2>
                        </div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                            <i class="fas fa-list-ol fs-4 text-primary"></i>
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
                            <span class="text-muted text-uppercase small fw-semibold">Senin</span>
                            <h2 class="mb-0 mt-1 fw-bold">{{ $senin }}</h2>
                        </div>
                        <div class="rounded-circle bg-info bg-opacity-10 p-3">
                            <i class="fas fa-sun fs-4 text-info"></i>
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
                            <span class="text-muted text-uppercase small fw-semibold">Jumat</span>
                            <h2 class="mb-0 mt-1 fw-bold">{{ $jumat }}</h2>
                        </div>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                            <i class="fas fa-moon fs-4 text-warning"></i>
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
                            <span class="text-muted text-uppercase small fw-semibold">Rata-rata</span>
                            <h2 class="mb-0 mt-1 fw-bold">{{ round($totalSesi / 5) }}</h2>
                        </div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-3">
                            <i class="fas fa-chart-line fs-4 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-table me-2 text-primary"></i> Daftar Sesi Pelajaran
                    </h5>
                </div>
                <div class="col-md-6">
                    <input type="text" id="searchSesi" class="form-control form-control-sm" placeholder="Cari hari atau sesi...">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table data-hide-sm="1 6" class="table table-hover align-middle mb-0" id="jamTable">
                    <thead class="table-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Hari</th>
                            <th>Sesi Ke</th>
                            <th>Jam Mulai</th>
                            <th>Jam Selesai</th>
                            <th>Durasi (menit)</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jamPelajaran as $index => $j)
                        <tr>
                            <td>{{ $index + 1 + ($jamPelajaran->currentPage() - 1) * $jamPelajaran->perPage() }}</td>
                            <td>
                                @php
                                    $badgeColor = [
                                        'Senin' => 'primary',
                                        'Selasa' => 'success',
                                        'Rabu' => 'info',
                                        'Kamis' => 'warning',
                                        'Jumat' => 'danger',
                                    ][$j->hari] ?? 'secondary';
                                @endphp
                                <span class="badge bg-{{ $badgeColor }}">{{ $j->hari }}</span>
                            </td>
                            <td>{{ $j->sesi_ke }}</td>
                            <td>{{ substr($j->jam_mulai,0,5) }}</td>
                            <td>{{ substr($j->jam_selesai,0,5) }}</td>
                            @php
                                $start = \Carbon\Carbon::parse($j->jam_mulai);
                                $end = \Carbon\Carbon::parse($j->jam_selesai);
                                $duration = $start->diffInMinutes($end);
                            @endphp
                            <td class="text-center">{{ $duration }} menit</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('jam-pelajaran.edit', $j->id) }}" class="btn btn-warning" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-danger" data-bs-toggle="tooltip" title="Hapus" onclick="confirmDelete('{{ route('jam-pelajaran.destroy', $j->id) }}', {{ Js::from($j->hari . ' Sesi ' . $j->sesi_ke) }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $jamPelajaran->links() }}
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>

<script>
    function confirmDelete(url, name) {
        if (confirm('Yakin ingin menghapus jadwal ' + name + '?\nData ini akan dihapus secara permanen.')) {
            let form = document.getElementById('deleteForm');
            form.action = url;
            form.submit();
        }
    }

    // Live search
    document.getElementById('searchSesi').addEventListener('keyup', function() {
        let search = this.value.toLowerCase();
        let rows = document.querySelectorAll('#jamTable tbody tr');
        rows.forEach(row => {
            let text = row.innerText.toLowerCase();
            row.style.display = text.includes(search) ? '' : 'none';
        });
    });

    // Tooltip
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    });
</script>
@endsection