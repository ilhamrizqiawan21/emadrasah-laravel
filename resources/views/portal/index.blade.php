@extends('layouts.app')

@section('title', 'Kelas & Jadwal Saya')

@section('content')
<x-page-header title="Kelas & Jadwal Saya">
    Ringkasan mengajar Anda: jadwal hari ini, kelas, dan pintasan kerja.
</x-page-header>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-semibold">Jadwal Hari Ini · {{ $hariIni }}</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-stack-sm table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Jam</th><th>Kelas</th><th>Mata Pelajaran</th><th>Ruang</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @forelse($jadwalHariIni as $j)
                    <tr>
                        <td data-label="Jam">{{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai, 0, 5) }}</td>
                        <td data-label="Kelas" class="fw-semibold">{{ $j->kelas->nama_kelas }}</td>
                        <td data-label="Mata Pelajaran">{{ $j->mapel->nama_mapel }}</td>
                        <td data-label="Ruang">{{ $j->ruang ?: '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('absensi-siswa.index', ['kelas_id' => $j->kelas_id]) }}" class="btn btn-sm btn-outline-primary">Absensi</a>
                            <a href="{{ route('nilai.index', ['kelas_id' => $j->kelas_id, 'mapel_id' => $j->mapel_id]) }}" class="btn btn-sm btn-outline-secondary">Nilai</a>
                        </td>
                    </tr>
                    @empty
                    <x-empty-row :colspan="5" icon="fa-mug-hot">Tidak ada jadwal mengajar hari ini.</x-empty-row>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-semibold">Kelas Saya</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-stack-sm table-hover align-middle mb-0">
                <thead class="table-light"><tr><th>Kelas</th><th>Peran</th><th class="text-center">Siswa Aktif</th><th class="text-end">Aksi</th></tr></thead>
                <tbody>
                    @forelse($kelasList as $k)
                    <tr>
                        <td data-label="Kelas" class="fw-semibold">{{ $k->nama_kelas }}</td>
                        <td data-label="Peran">
                            @if($k->guru_pembimbing_id === $guruId)<span class="badge bg-primary">Wali kelas</span>@endif
                            @foreach($mapelPerKelas[$k->id] ?? [] as $nama)<span class="badge bg-secondary">{{ $nama }}</span>@endforeach
                        </td>
                        <td data-label="Siswa Aktif" class="text-center">{{ $jumlahSiswa[$k->id] ?? 0 }}</td>
                        <td class="text-end">
                            <a href="{{ route('portal.kelas', $k) }}" class="btn btn-sm btn-outline-info">Daftar siswa</a>
                            <a href="{{ route('absensi-siswa.index', ['kelas_id' => $k->id]) }}" class="btn btn-sm btn-outline-primary">Absensi</a>
                        </td>
                    </tr>
                    @empty
                    <x-empty-row :colspan="4" icon="fa-school">Anda belum menjadi wali kelas atau punya jadwal mengajar.</x-empty-row>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3"><h5 class="mb-0 fw-semibold">Jadwal Mingguan</h5></div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-stack-sm align-middle mb-0">
                <thead class="table-light"><tr><th>Hari</th><th>Jam</th><th>Kelas</th><th>Mata Pelajaran</th></tr></thead>
                <tbody>
                    @forelse($jadwalPerHari as $hari => $items)
                        @foreach($items as $j)
                        <tr>
                            <td data-label="Hari" class="fw-semibold">{{ $loop->first ? $hari : '' }}</td>
                            <td data-label="Jam">{{ substr($j->jam_mulai, 0, 5) }}–{{ substr($j->jam_selesai, 0, 5) }}</td>
                            <td data-label="Kelas">{{ $j->kelas->nama_kelas }}</td>
                            <td data-label="Mata Pelajaran">{{ $j->mapel->nama_mapel }}</td>
                        </tr>
                        @endforeach
                    @empty
                    <x-empty-row :colspan="4" icon="fa-calendar">Belum ada jadwal mengajar.</x-empty-row>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
