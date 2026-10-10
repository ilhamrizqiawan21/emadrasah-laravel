@extends('layouts.app')

@section('title', 'Detail Siswa - ' . $siswa->nama_lengkap)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold"><i class="fas fa-id-card me-2 text-primary"></i> Detail Siswa</h1>
            <p class="text-muted mb-0">Informasi biodata lengkap siswa</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('siswa.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Kembali
            </a>
            <a href="{{ route('siswa.edit', $siswa) }}" class="btn btn-warning text-white">
                <i class="fas fa-edit me-1"></i> Edit Data
            </a>
            <a href="{{ route('buku-induk.show', $siswa) }}" class="btn btn-info text-white">
                <i class="fas fa-book-open me-1"></i> Buku Induk
            </a>
            <a href="{{ route('raport.manage', $siswa) }}" class="btn btn-primary">
                <i class="fas fa-file-invoice me-1"></i> Nilai Raport
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Kolom Kiri: Profil Singkat --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="mb-3">
                    @if($siswa->foto)
                        <img src="{{ route('files.show', ['path' => $siswa->foto]) }}" class="rounded shadow-sm" style="width: 140px; height: 180px; object-fit: cover;">
                    @else
                        <div class="bg-light d-inline-flex align-items-center justify-content-center rounded shadow-sm" style="width: 140px; height: 180px;">
                            <i class="fas fa-user-graduate fa-4x text-muted"></i>
                        </div>
                    @endif
                </div>
                <h4 class="fw-bold mb-1">{{ $siswa->nama_lengkap }}</h4>
                <p class="text-muted mb-2">NIS: {{ $siswa->nis }} | NISN: {{ $siswa->nisn ?? '-' }}</p>
                <div>
                    <span class="badge {{ $siswa->status === 'Aktif' ? 'bg-success' : 'bg-secondary' }} px-3 py-2">
                        {{ $siswa->status ?? 'Aktif' }}
                    </span>
                    <span class="badge bg-primary px-3 py-2 ms-1">
                        {{ $siswa->kelas->nama_kelas ?? 'Belum ada kelas' }}
                    </span>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3 p-3">
                <h6 class="fw-bold text-muted mb-3"><i class="fas fa-clock-rotate-left me-2"></i> Akses Cepat</h6>
                <div class="d-grid gap-2">
                    <a href="{{ route('buku-induk.export-pdf', $siswa) }}" target="_blank" class="btn btn-outline-danger btn-sm text-start">
                        <i class="fas fa-file-pdf me-2"></i> Cetak PDF Buku Induk
                    </a>
                    <a href="{{ route('kartu-pelajar.siswa', $siswa) }}" target="_blank" class="btn btn-outline-secondary btn-sm text-start">
                        <i class="fas fa-id-card me-2"></i> Cetak Kartu Pelajar
                    </a>
                    <a href="{{ route('surat-siswa.index', $siswa) }}" class="btn btn-outline-primary btn-sm text-start">
                        <i class="fas fa-envelope-open-text me-2"></i> Surat Keterangan Aktif
                    </a>
                    <a href="{{ route('raport.export-pdf', $siswa) }}" target="_blank" class="btn btn-outline-success btn-sm text-start">
                        <i class="fas fa-file-invoice me-2"></i> Cetak PDF Raport
                    </a>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan: Data Detail --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-bold text-primary border-bottom pb-2 mb-3">
                    <i class="fas fa-user me-2"></i> Biodata Pribadi
                </h5>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small">Nomor Induk Siswa (NIS)</label>
                        <div class="fw-semibold">{{ $siswa->nis }}</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">NISN</label>
                        <div class="fw-semibold">{{ $siswa->nisn ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">NIK (KTP/KK)</label>
                        <div class="fw-semibold">{{ $siswa->nik ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Jenis Kelamin</label>
                        <div class="fw-semibold">{{ $siswa->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Tempat, Tanggal Lahir</label>
                        <div class="fw-semibold">
                            {{ $siswa->tempat_lahir ?? '-' }}, {{ $siswa->tanggal_lahir ? \Carbon\Carbon::parse($siswa->tanggal_lahir)->translatedFormat('d F Y') : '-' }}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Agama</label>
                        <div class="fw-semibold">{{ $siswa->agama ?? 'Islam' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">No. Telepon / HP</label>
                        <div class="fw-semibold">{{ $siswa->hp ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Kelas</label>
                        <div class="fw-semibold">{{ $siswa->kelas->nama_kelas ?? '-' }} (Tingkat {{ $siswa->kelas->tingkat ?? '-' }})</div>
                    </div>
                    <div class="col-12">
                        <label class="text-muted small">Alamat Tinggal</label>
                        <div class="fw-semibold">{{ $siswa->alamat ?? '-' }}</div>
                    </div>
                </div>

                <h5 class="fw-bold text-primary border-bottom pb-2 mt-4 mb-3">
                    <i class="fas fa-users me-2"></i> Data Orang Tua / Wali
                </h5>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <label class="text-muted small">Nama Ayah</label>
                        <div class="fw-semibold">{{ $siswa->orangTuaWali->nama_ayah ?? ($siswa->nama_orang_tua ?? '-') }}</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Pekerjaan Ayah</label>
                        <div class="fw-semibold">{{ $siswa->orangTuaWali->pekerjaan_ayah ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Nama Ibu</label>
                        <div class="fw-semibold">{{ $siswa->orangTuaWali->nama_ibu ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">Pekerjaan Ibu</label>
                        <div class="fw-semibold">{{ $siswa->orangTuaWali->pekerjaan_ibu ?? '-' }}</div>
                    </div>
                    <div class="col-sm-6">
                        <label class="text-muted small">No. HP Orang Tua</label>
                        <div class="fw-semibold">{{ $siswa->orangTuaWali->no_telepon_ayah ?? ($siswa->orangTuaWali->no_telepon_ibu ?? '-') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
