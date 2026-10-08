@extends('layouts.app')

@section('title', 'Tambah Jam Pelajaran')

@section('content')
<div class="container-fluid px-0">
    <div class="mb-4">
        <a href="{{ route('jam-pelajaran.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-plus-circle me-2 text-primary"></i> Tambah Jam Pelajaran
                    </h5>
                    <p class="text-muted mb-0 mt-1">Isi form berikut untuk menambah sesi pelajaran baru</p>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('jam-pelajaran.store') }}" id="formJam">
                        @csrf
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Hari <span class="text-danger">*</span></label>
                                <select name="hari" class="form-select @error('hari') is-invalid @enderror" required>
                                    <option value="">-- Pilih Hari --</option>
                                    <option value="Senin" {{ old('hari')=='Senin' ? 'selected' : '' }}>Senin</option>
                                    <option value="Selasa" {{ old('hari')=='Selasa' ? 'selected' : '' }}>Selasa</option>
                                    <option value="Rabu" {{ old('hari')=='Rabu' ? 'selected' : '' }}>Rabu</option>
                                    <option value="Kamis" {{ old('hari')=='Kamis' ? 'selected' : '' }}>Kamis</option>
                                    <option value="Jumat" {{ old('hari')=='Jumat' ? 'selected' : '' }}>Jumat</option>
                                </select>
                                @error('hari')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Sesi Ke <span class="text-danger">*</span></label>
                                <input type="number" name="sesi_ke" class="form-control @error('sesi_ke') is-invalid @enderror" value="{{ old('sesi_ke') }}" min="1" required>
                                @error('sesi_ke')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div class="form-text">Nomor urut sesi, misal: 1, 2, 3, dst</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jam Mulai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_mulai" class="form-control @error('jam_mulai') is-invalid @enderror" value="{{ old('jam_mulai') }}" required>
                                @error('jam_mulai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Jam Selesai <span class="text-danger">*</span></label>
                                <input type="time" name="jam_selesai" class="form-control @error('jam_selesai') is-invalid @enderror" value="{{ old('jam_selesai') }}" required>
                                @error('jam_selesai')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="text-end">
                            <button type="submit" class="btn btn-primary px-4" id="btnSubmit">
                                <i class="fas fa-save me-1"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card bg-light mt-4 border-0">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="fas fa-info-circle fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Informasi</h6>
                            <p class="mb-0 small text-muted">
                                Pastikan jam mulai dan jam selesai tidak tumpang tindih dengan sesi lain pada hari yang sama.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('formJam')?.addEventListener('submit', function(e) {
        const btn = document.getElementById('btnSubmit');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Menyimpan...';
    });
</script>
@endsection