@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col">
            <h2 class="em-page-title">Tambah Data Buku Induk</h2>
            <p class="text-muted">Input data lengkap siswa sesuai format Kurikulum Merdeka.</p>
        </div>
    </div>

    <form action="{{ route('buku-induk.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        {{-- Custom Tabs Nav --}}
        <ul class="nav nav-pills mb-4 em-tabs" id="bukuIndukTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#tab-diri" type="button">A. Keterangan Diri</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-tinggal" type="button">B. Tempat Tinggal</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-ortu" type="button">C. Orang Tua/Wali</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-pendidikan" type="button">D. Pendidikan & Perkembangan</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-lain" type="button">Lain-lain</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="pill" data-bs-target="#tab-dokumen" type="button">E-Document</button>
            </li>
        </ul>

        <div class="tab-content border-0 shadow-sm card p-4 mb-4">
            
            {{-- TAB A: KETERANGAN DIRI --}}
            <div class="tab-pane fade show active" id="tab-diri">
                <h5 class="mb-3 text-primary border-bottom pb-2">Keterangan Tentang Diri Peserta Didik</h5>
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label">No. Urut Induk</label>
                        <input type="number" name="no_urut" class="form-control" value="{{ old('no_urut') }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" required value="{{ old('nama_lengkap') }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Nama Panggilan</label>
                        <input type="text" name="nama_panggilan" class="form-control" value="{{ old('nama_panggilan') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NIS (Lokal)</label>
                        <input type="text" name="nis" class="form-control" required value="{{ old('nis') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NISN</label>
                        <input type="text" name="nisn" class="form-control" value="{{ old('nisn') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">NIK (16 Digit)</label>
                        <input type="text" name="nik" class="form-control" maxlength="16" value="{{ old('nik') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select">
                            <option value="L" {{ old('jenis_kelamin') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jenis_kelamin') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Tempat Lahir</label>
                        <input type="text" name="tempat_lahir" class="form-control" value="{{ old('tempat_lahir') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" class="form-control" value="{{ old('tanggal_lahir') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Agama</label>
                        <input type="text" name="agama" class="form-control" placeholder="Islam" value="{{ old('agama', 'Islam') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Golongan Darah</label>
                        <select name="golongan_darah" class="form-select">
                            <option value="Tidak Tahu">Tidak Tahu</option>
                            <option value="A">A</option>
                            <option value="B">B</option>
                            <option value="AB">AB</option>
                            <option value="O">O</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- TAB B: TEMPAT TINGGAL --}}
            <div class="tab-pane fade" id="tab-tinggal">
                <h5 class="mb-3 text-primary border-bottom pb-2">Keterangan Tempat Tinggal</h5>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">Alamat Jalan</label>
                        <textarea name="alamat" class="form-control" rows="2">{{ old('alamat') }}</textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">RT / RW</label>
                        <div class="input-group">
                            <input type="text" name="rt" class="form-control" placeholder="01" value="{{ old('rt') }}">
                            <input type="text" name="rw" class="form-control" placeholder="05" value="{{ old('rw') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Desa / Kelurahan</label>
                        <input type="text" name="desa_kelurahan" class="form-control" value="{{ old('desa_kelurahan') }}">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Kecamatan</label>
                        <input type="text" name="kecamatan" class="form-control" value="{{ old('kecamatan') }}">
                    </div>
                </div>
            </div>

            {{-- TAB C: ORANG TUA --}}
            <div class="tab-pane fade" id="tab-ortu">
                <h5 class="mb-3 text-primary border-bottom pb-2">Keterangan Tentang Ayah Kandung</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Nama Ayah</label>
                        <input type="text" name="nama_ayah" class="form-control" value="{{ old('nama_ayah') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pekerjaan Ayah</label>
                        <input type="text" name="pekerjaan_ayah" class="form-control" value="{{ old('pekerjaan_ayah') }}">
                    </div>
                </div>
                
                <h5 class="mb-3 text-primary border-bottom pb-2">Keterangan Tentang Ibu Kandung</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nama Ibu</label>
                        <input type="text" name="nama_ibu" class="form-control" value="{{ old('nama_ibu') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Pekerjaan Ibu</label>
                        <input type="text" name="pekerjaan_ibu" class="form-control" value="{{ old('pekerjaan_ibu') }}">
                    </div>
                </div>
            </div>

            {{-- TAB D: PENDIDIKAN --}}
            <div class="tab-pane fade" id="tab-pendidikan">
                <h5 class="mb-3 text-primary border-bottom pb-2">Keterangan Pendidikan Sebelumnya</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Lulusan Dari (SD/MI)</label>
                        <input type="text" name="asal_sekolah" class="form-control" value="{{ old('asal_sekolah') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">No. Ijazah</label>
                        <input type="text" name="no_ijazah_asal" class="form-control" value="{{ old('no_ijazah_asal') }}">
                    </div>
                </div>

                <h5 class="mb-3 text-primary border-bottom pb-2">Penerimaan Peserta Didik Baru</h5>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Diterima di Kelas</label>
                        <select name="kelas_id" class="form-select">
                            @foreach($kelas as $k)
                            <option value="{{ $k->id }}">{{ $k->nama_kelas }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tahun Pelajaran</label>
                        <select name="tahun_pelajaran_id" class="form-select">
                            @foreach($tahunPelajaran as $tp)
                            <option value="{{ $tp->id }}">{{ $tp->kode }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- TAB LAIN-LAIN --}}
            <div class="tab-pane fade" id="tab-lain">
                <h5 class="mb-3 text-primary border-bottom pb-2">Keterangan Lain-lain</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Hobi Olahraga</label>
                        <input type="text" name="hobi_olahraga" class="form-control" value="{{ old('hobi_olahraga') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Foto Siswa</label>
                        <input type="file" name="foto" class="form-control">
                    </div>
                </div>
            </div>

            {{-- TAB DOKUMEN --}}
            <div class="tab-pane fade" id="tab-dokumen">
                <h5 class="mb-3 text-primary border-bottom pb-2">Brankas Dokumen Digital (Scan)</h5>
                <div class="alert alert-info py-2">
                    <i class="fas fa-info-circle me-2"></i> File yang diunggah akan tersimpan di brankas digital siswa.
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Scan Akta Kelahiran</label>
                        <input type="file" name="dokumen[Akta Kelahiran]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Scan Kartu Keluarga</label>
                        <input type="file" name="dokumen[Kartu Keluarga]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Scan Ijazah SD/MI</label>
                        <input type="file" name="dokumen[Ijazah]" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                </div>
            </div>

        </div>

        <div class="text-end">
            <button type="reset" class="btn btn-light px-4 me-2">Reset</button>
            <button type="submit" class="btn btn-primary px-5">Simpan Data Buku Induk</button>
        </div>
    </form>
</div>

<style>
    .em-tabs .nav-link {
        background: #f8f9fa;
        color: #495057;
        margin-right: 5px;
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 500;
        border: 1px solid #dee2e6;
    }
    .em-tabs .nav-link.active {
        background: var(--em-primary);
        color: white;
        border-color: var(--em-primary);
    }
</style>
@endsection
