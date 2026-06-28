@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="em-page-title">Edit Data Buku Induk: {{ $siswa->nama_lengkap }}</h2>
            <p class="text-muted">Perbarui data lengkap siswa sesuai format Kurikulum Merdeka.</p>
        </div>
    </div>

    <form action="{{ route('buku-induk.update', $siswa) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <ul class="nav nav-pills mb-4 em-tabs" id="bukuIndukTab" role="tablist">
            <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-diri" type="button">A. Keterangan Diri</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-tinggal" type="button">B. Tempat Tinggal</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ortu" type="button">C. Orang Tua/Wali</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-pendidikan" type="button">D. Pendidikan & Perkembangan</button></li>
            <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-lain" type="button">Lain-lain</button></li>
        </ul>

        <div class="tab-content border-0 shadow-sm card p-4 mb-4">
            
            <div class="tab-pane fade show active" id="tab-diri">
                <h5 class="mb-3 text-primary border-bottom pb-2">Keterangan Tentang Diri Peserta Didik</h5>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">No. Urut Induk</label>
                        <input type="number" name="no_urut" class="form-control" value="{{ old('no_urut', $siswa->no_urut) }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" required value="{{ old('nama_lengkap', $siswa->nama_lengkap) }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nama Panggilan</label>
                        <input type="text" name="nama_panggilan" class="form-control" value="{{ old('nama_panggilan', $siswa->nama_panggilan) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NIS (Lokal)</label>
                        <input type="text" name="nis" class="form-control" required value="{{ old('nis', $siswa->nis) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NISN</label>
                        <input type="text" name="nisn" class="form-control" value="{{ old('nisn', $siswa->nisn) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NIK</label>
                        <input type="text" name="nik" class="form-control" maxlength="16" value="{{ old('nik', $siswa->nik) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="L" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin', $siswa->jenis_kelamin) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir', $siswa->tempat_lahir) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir', $siswa->tanggal_lahir ? $siswa->tanggal_lahir->format('Y-m-d') : '') }}">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-tinggal">
                <h5 class="mb-3 text-primary border-bottom pb-2">Keterangan Tempat Tinggal</h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Alamat Jalan</label>
                        <textarea name="alamat" class="form-control" rows="2">{{ old('alamat', $siswa->alamat) }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">RT / RW</label>
                        <div class="input-group">
                            <input type="text" name="rt" class="form-control" value="{{ old('rt', $siswa->rt) }}">
                            <input type="text" name="rw" class="form-control" value="{{ old('rw', $siswa->rw) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Desa / Kelurahan</label>
                        <input type="text" name="desa_kelurahan" class="form-control" value="{{ old('desa_kelurahan', $siswa->desa_kelurahan) }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Kecamatan</label>
                        <input type="text" name="kecamatan" class="form-control" value="{{ old('kecamatan', $siswa->kecamatan) }}">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-ortu">
                <h5 class="mb-3 text-primary border-bottom pb-2">Keterangan Tentang Orang Tua</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Ayah</label>
                        <input type="text" name="nama_ayah" class="form-control" value="{{ old('nama_ayah', $siswa->orangTuaWali->nama_ayah ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Nama Ibu</label>
                        <input type="text" name="nama_ibu" class="form-control" value="{{ old('nama_ibu', $siswa->orangTuaWali->nama_ibu ?? '') }}">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-pendidikan">
                <h5 class="mb-3 text-primary border-bottom pb-2">Keterangan Pendidikan</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Sekolah Asal</label>
                        <input type="text" name="asal_madrasah" class="form-control" value="{{ old('asal_madrasah', $siswa->perkembangan->asal_madrasah ?? '') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No. Ijazah Asal</label>
                        <input type="text" name="no_ijazah_asal" class="form-control" value="{{ old('no_ijazah_asal', $siswa->perkembangan->no_ijazah_asal ?? '') }}">
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-lain">
                <h5 class="mb-3 text-primary border-bottom pb-2">Lain-lain</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="Aktif" {{ $siswa->status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Lulus" {{ $siswa->status == 'Lulus' ? 'selected' : '' }}>Lulus</option>
                            <option value="Pindah" {{ $siswa->status == 'Pindah' ? 'selected' : '' }}>Pindah</option>
                        </select>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-end">
            <a href="{{ route('buku-induk.index') }}" class="btn btn-light px-4 me-2">Batal</a>
            <button type="submit" class="btn btn-primary px-5">Simpan Perubahan</button>
        </div>
    </form>
</div>
@endsection
