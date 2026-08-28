@extends('layouts.app')

@section('title', 'Edit Data Siswa')

@section('content')
<x-page-header
    title="Edit Data Siswa"
    subtitle="Perbarui informasi data diri dan akademik siswa."
/>

<form action="{{ route('siswa.update', $siswa) }}" method="POST" class="em-form-layout">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-lg-8">
            <x-form-section title="Informasi Pribadi" subtitle="Identitas dasar siswa dan data kependudukan.">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="nama_lengkap" class="form-control @error('nama_lengkap') is-invalid @enderror" value="{{ old('nama_lengkap', $siswa->nama_lengkap) }}" required>
                            @error('nama_lengkap') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Nama Orang Tua</label>
                            <input type="text" name="nama_orang_tua" class="form-control @error('nama_orang_tua') is-invalid @enderror" value="{{ old('nama_orang_tua', $siswa->nama_orang_tua) }}">
                            @error('nama_orang_tua') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIS <span class="text-danger">*</span></label>
                            <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror" value="{{ old('nis', $siswa->nis) }}" required>
                            @error('nis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NISN</label>
                            <input type="text" name="nisn" class="form-control @error('nisn') is-invalid @enderror" value="{{ old('nisn', $siswa->nisn) }}" data-mask="nisn" inputmode="numeric" maxlength="10">
                            @error('nisn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tempat Lahir</label>
                            <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $siswa->tempat_lahir) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $siswa->tanggal_lahir?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Jenis Kelamin <span class="text-danger">*</span></label>
                            <select name="jenis_kelamin" class="form-select" required>
                                <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">NIK (No. KTP)</label>
                            <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror" value="{{ old('nik', $siswa->nik) }}" data-mask="nik" inputmode="numeric" maxlength="16">
                            @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-semibold">Alamat Lengkap</label>
                            <textarea name="alamat" class="form-control" rows="3">{{ old('alamat', $siswa->alamat) }}</textarea>
                        </div>
                    </div>
            </x-form-section>
        </div>
        
        <div class="col-lg-4">
            <x-form-section title="Data Akademik" subtitle="Penempatan kelas, tahun pelajaran, dan status.">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Kelas <span class="text-danger">*</span></label>
                        <select name="kelas_id" class="form-select" required>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}" {{ old('kelas_id', $siswa->kelas_id) == $k->id ? 'selected' : '' }}>{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" class="form-select">
                            @foreach($tahunPelajaran as $tp)
                                <option value="{{ $tp->id }}" {{ old('tahun_pelajaran_id', $siswa->tahun_pelajaran_id) == $tp->id ? 'selected' : '' }}>{{ $tp->kode }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Status Siswa <span class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            @foreach(['Aktif', 'Lulus', 'Pindah', 'Keluar'] as $st)
                                <option value="{{ $st }}" {{ old('status', $siswa->status) == $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold">No. HP / WhatsApp</label>
                        <input type="text" name="hp" class="form-control @error('hp') is-invalid @enderror" value="{{ old('hp', $siswa->hp) }}" data-mask="phone" inputmode="tel">
                        @error('hp') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
            </x-form-section>
            
            <x-form-actions
                :back-url="route('siswa.index')"
                submit-label="Perbarui Data"
                confirm="Simpan perubahan data siswa?"
            />
        </div>
    </div>
</form>
@endsection
