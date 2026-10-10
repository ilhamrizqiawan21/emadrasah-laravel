@extends('layouts.app')

@section('title', 'Absensi Siswa')

@section('content')
<x-page-header title="Absensi Siswa Harian">
    Catat kehadiran siswa per kelas.
    <x-slot:actions>
        <a href="{{ route('absensi-siswa.rekap', array_filter(['kelas_id' => $kelas?->id])) }}" class="btn btn-outline-info">
            <i class="fas fa-chart-line me-1"></i> Rekap Bulanan
        </a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm mb-4 border-0">
    <div class="card-body">
        <form method="GET" action="{{ route('absensi-siswa.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label fw-semibold" for="kelas_id">Kelas</label>
                <select name="kelas_id" id="kelas_id" class="form-select">
                    @foreach($kelasList as $k)
                        <option value="{{ $k->id }}" @selected($kelas?->id === $k->id)>{{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold" for="tanggal">Tanggal</label>
                <input type="date" name="tanggal" id="tanggal" class="form-control" value="{{ $tanggal->format('Y-m-d') }}" max="{{ today()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search me-1"></i> Tampilkan</button>
            </div>
        </form>
    </div>
</div>

@if(! $kelas)
    <div class="card border-0 shadow-sm"><div class="card-body text-center text-muted py-5">
        <i class="fas fa-school fa-3x mb-3 opacity-25"></i>
        <p class="mb-0">Belum ada kelas yang bisa Anda kelola. Minta admin menetapkan Anda sebagai wali kelas atau menambahkan jadwal.</p>
    </div></div>
@else
<form method="POST" action="{{ route('absensi-siswa.store') }}" id="formAbsensiSiswa">
    @csrf
    <input type="hidden" name="kelas_id" value="{{ $kelas->id }}">
    <input type="hidden" name="tanggal" value="{{ $tanggal->format('Y-m-d') }}">

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="mb-0 fw-semibold">{{ $kelas->nama_kelas }} · {{ $tanggal->translatedFormat('l, d F Y') }}</h5>
            @if($siswa->isNotEmpty())
            <button type="button" class="btn btn-sm btn-outline-success" id="btnSemuaHadir"><i class="fas fa-check-double me-1"></i> Semua hadir</button>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-stack-sm table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50" class="text-center">No</th>
                            <th>NIS</th>
                            <th>Nama Siswa</th>
                            <th width="160" class="text-center">Status</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswa as $i => $s)
                        @php($status = old("status.$s->id", $absensi[$s->id]->status ?? 'hadir'))
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td>{{ $s->nis }}</td>
                            <td class="fw-semibold">{{ $s->nama_lengkap }}</td>
                            <td data-label="Status">
                                <div class="status-field" data-status="{{ $status }}">
                                    <i class="fas status-field__icon" aria-hidden="true"></i>
                                    <select name="status[{{ $s->id }}]" class="form-select form-select-sm status-select" aria-label="Status {{ $s->nama_lengkap }}">
                                        @foreach(['hadir' => 'Hadir', 'izin' => 'Izin', 'sakit' => 'Sakit', 'alpha' => 'Alpha'] as $val => $label)
                                            <option value="{{ $val }}" @selected($status === $val)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </td>
                            <td data-label="Keterangan">
                                <input type="text" name="keterangan[{{ $s->id }}]" class="form-control form-control-sm" maxlength="255" value="{{ old("keterangan.$s->id", $absensi[$s->id]->keterangan ?? '') }}" placeholder="Opsional">
                            </td>
                        </tr>
                        @empty
                        <x-empty-row :colspan="5" icon="fa-user-graduate">Belum ada siswa aktif di kelas ini.</x-empty-row>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($siswa->isNotEmpty())
        <div class="card-footer bg-white text-end py-3">
            <button type="submit" class="btn btn-primary px-4"><i class="fas fa-save me-2"></i> Simpan Absensi</button>
        </div>
        @endif
    </div>
</form>
@endif
@endsection

@push('scripts')
<script>
    document.querySelectorAll('.status-field select').forEach(function (select) {
        select.addEventListener('change', function () {
            select.closest('.status-field').dataset.status = select.value;
        });
    });
    document.getElementById('btnSemuaHadir')?.addEventListener('click', function () {
        document.querySelectorAll('.status-field select').forEach(function (select) {
            select.value = 'hadir';
            select.closest('.status-field').dataset.status = 'hadir';
        });
    });
</script>
@endpush
