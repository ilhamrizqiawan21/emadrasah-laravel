@extends('layouts.app')

@section('title', 'Kedisiplinan dan BK')

@section('content')
<x-page-header title="Kedisiplinan & BK">
    Poin pelanggaran dan catatan konseling siswa aktif. Ambang peringatan: {{ $ambang }} poin.
    <x-slot:actions>
        <a href="{{ route('kedisiplinan.jenis.index') }}" class="btn btn-outline-secondary"><i class="fas fa-list me-1"></i> Jenis Pelanggaran</a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3">
        <form method="GET" action="{{ route('kedisiplinan.index') }}" class="row g-2 align-items-center">
            <div class="col-md-4"><input type="search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Nama atau NIS" aria-label="Cari siswa"></div>
            <div class="col-md-3">
                <select name="kelas_id" class="form-select" aria-label="Kelas">
                    <option value="">Semua kelas</option>
                    @foreach($kelas as $k)<option value="{{ $k->id }}" @selected(request('kelas_id') == $k->id)>{{ $k->nama_kelas }}</option>@endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="form-check"><input class="form-check-input" type="checkbox" name="bermasalah" value="1" id="bermasalah" @checked(request()->boolean('bermasalah'))><label class="form-check-label" for="bermasalah">Hanya yang punya poin</label></div>
            </div>
            <div class="col-md-2"><button class="btn btn-outline-primary w-100">Terapkan</button></div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-stack-sm align-middle mb-0">
            <thead class="table-light"><tr><th>Siswa</th><th>Kelas</th><th class="text-end">Total poin</th><th></th><th class="text-end">Aksi</th></tr></thead>
            <tbody>
                @forelse($daftar as $s)
                    @php $poin = (int) $s->total_poin; @endphp
                    <tr>
                        <td data-label="Siswa"><div class="fw-semibold">{{ $s->nama_lengkap }}</div><div class="small text-muted">NIS {{ $s->nis }}</div></td>
                        <td data-label="Kelas">{{ $s->kelas?->nama_kelas }}</td>
                        <td data-label="Total poin" class="text-end fw-semibold">{{ $poin }}</td>
                        <td data-label="">@if($poin >= $ambang)<span class="badge bg-danger-subtle text-danger-emphasis">Melewati ambang</span>@endif</td>
                        <td class="text-end"><a href="{{ route('kedisiplinan.siswa', $s) }}" class="btn btn-sm btn-outline-primary">Buka</a></td>
                    </tr>
                @empty
                    <x-empty-row :colspan="5" icon="fa-scale-balanced">Tidak ada siswa yang cocok.</x-empty-row>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($daftar->hasPages())<div class="card-footer bg-white">{{ $daftar->links() }}</div>@endif
</div>
@endsection
