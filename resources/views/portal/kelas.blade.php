@extends('layouts.app')

@section('title', 'Daftar Siswa '.$kelas->nama_kelas)

@section('content')
<x-page-header title="Siswa {{ $kelas->nama_kelas }}">
    {{ $siswa->count() }} siswa aktif.
    <x-slot:actions>
        <a href="{{ route('portal.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left me-1"></i> Kembali</a>
        <a href="{{ route('absensi-siswa.index', ['kelas_id' => $kelas->id]) }}" class="btn btn-outline-primary">Absensi</a>
    </x-slot:actions>
</x-page-header>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-stack-sm table-hover align-middle mb-0">
                <thead class="table-light"><tr><th width="50" class="text-center">No</th><th>NIS</th><th>NISN</th><th>Nama</th><th class="text-center">L/P</th><th>Kontak Orang Tua</th></tr></thead>
                <tbody>
                    @forelse($siswa as $i => $s)
                    <tr>
                        <td class="text-center">{{ $i + 1 }}</td>
                        <td data-label="NIS">{{ $s->nis }}</td>
                        <td data-label="NISN">{{ $s->nisn ?: '—' }}</td>
                        <td data-label="Nama" class="fw-semibold">{{ $s->nama_lengkap }}</td>
                        <td data-label="L/P" class="text-center">{{ $s->jenis_kelamin ?: '—' }}</td>
                        <td data-label="Kontak">{{ $s->hp ?: ($s->no_telepon ?: '—') }}</td>
                    </tr>
                    @empty
                    <x-empty-row :colspan="6" icon="fa-user-graduate">Belum ada siswa aktif di kelas ini.</x-empty-row>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
