@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-6">
            <h2 class="em-page-title">Profil Buku Induk Siswa</h2>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('buku-induk.index') }}" class="btn btn-light border me-2">Kembali</a>
            <a href="{{ route('buku-induk.export-pdf', $siswa) }}" target="_blank" class="btn btn-danger me-2">
                <i class="fas fa-file-pdf me-2"></i>Cetak Buku Induk
            </a>
            <a href="{{ route('buku-induk.edit', $siswa) }}" class="btn btn-warning text-white">Edit Data</a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-4 mb-4">
                <div class="mb-3">
                    @if($siswa->foto)
                        <img src="{{ route('files.show', ['path' => $siswa->foto]) }}" class="rounded shadow-sm" style="width: 150px; height: 200px; object-fit: cover;">
                    @else
                        <div class="bg-light d-inline-block rounded shadow-sm" style="width: 150px; height: 200px; line-height: 200px;">
                            <i class="fas fa-user-tie fa-4x text-muted"></i>
                        </div>
                    @endif
                </div>
                <h4 class="mb-1">{{ $siswa->nama_lengkap }}</h4>
                <p class="text-muted mb-0">NIS: {{ $siswa->nis }} | NISN: {{ $siswa->nisn ?? '-' }}</p>
                <span class="badge {{ $siswa->status == 'Aktif' ? 'bg-success' : 'bg-secondary' }} mt-2">
                    {{ $siswa->status }}
                </span>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card border-0 shadow-sm p-4">
                <h5 class="text-primary border-bottom pb-2 mb-3">Informasi Utama</h5>
                <table class="table table-sm table-borderless mb-4">
                    <tr><th width="30%">Tempat, Tgl Lahir</th><td>{{ $siswa->tempat_lahir ?? '-' }}, {{ $siswa->tanggal_lahir ? $siswa->tanggal_lahir->translatedFormat('d F Y') : '-' }}</td></tr>
                    <tr><th>Jenis Kelamin</th><td>{{ $siswa->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</td></tr>
                    <tr><th>Agama</th><td>{{ $siswa->agama ?? 'Islam' }}</td></tr>
                    <tr><th>Alamat</th><td>{{ $siswa->alamat ?? '-' }} (RT {{ $siswa->rt ?? '0' }}/RW {{ $siswa->rw ?? '0' }}) {{ $siswa->desa_kelurahan }}, {{ $siswa->kecamatan }}</td></tr>
                </table>

                <h5 class="text-primary border-bottom pb-2 mb-3">Orang Tua / Wali</h5>
                <table class="table table-sm table-borderless mb-4">
                    <tr><th width="30%">Nama Ayah</th><td>{{ $siswa->orangTuaWali->nama_ayah ?? '-' }}</td></tr>
                    <tr><th>Pekerjaan Ayah</th><td>{{ $siswa->orangTuaWali->pekerjaan_ayah ?? '-' }}</td></tr>
                    <tr><th>Nama Ibu</th><td>{{ $siswa->orangTuaWali->nama_ibu ?? '-' }}</td></tr>
                    <tr><th>Pekerjaan Ibu</th><td>{{ $siswa->orangTuaWali->pekerjaan_ibu ?? '-' }}</td></tr>
                </table>

                <h5 class="text-primary border-bottom pb-2 mb-3">Pendidikan Sebelumnya</h5>
                <table class="table table-sm table-borderless mb-4">
                    <tr><th width="30%">Sekolah Asal</th><td>{{ $siswa->perkembangan->asal_madrasah ?? '-' }}</td></tr>
                    <tr><th>No. Ijazah</th><td>{{ $siswa->perkembangan->no_ijazah_asal ?? '-' }}</td></tr>
                </table>

                <h5 class="text-primary border-bottom pb-2 mb-3">Brankas Dokumen Digital</h5>
                <div class="row g-2">
                    @forelse($siswa->dokumen as $dok)
                    <div class="col-md-4">
                        <div class="border rounded p-2 text-center bg-light">
                            <i class="fas fa-file-pdf fa-2x text-danger mb-2"></i>
                            <div class="small fw-bold">{{ $dok->jenis_dokumen }}</div>
                            <a href="{{ route('files.show', ['path' => $dok->file_path]) }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2 py-0">Buka File</a>
                        </div>
                    </div>
                    @empty
                    <div class="col-12 text-muted small">Belum ada dokumen yang diunggah.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
