@extends('layouts.app')

@section('title', 'Edit Sarana')

@section('content')
<x-page-header :title="'Edit Sarana: '.($sarana->nama_sarana)">
    <x-slot:actions>
        <a href="{{ route('sarana.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('sarana.update', $sarana) }}" enctype="multipart/form-data">
                    @csrf @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kode Sarana <span class="text-danger">*</span></label>
                            <input type="text" name="kode_sarana" class="form-control @error('kode_sarana') is-invalid @enderror" value="{{ old('kode_sarana', $sarana->kode_sarana) }}" required>
                            @error('kode_sarana')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Sarana <span class="text-danger">*</span></label>
                            <input type="text" name="nama_sarana" class="form-control @error('nama_sarana') is-invalid @enderror" value="{{ old('nama_sarana', $sarana->nama_sarana) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kategori <span class="text-danger">*</span></label>
                            <select name="kategori_id" class="form-select" required>
                                @foreach($kategori as $kat)
                                <option value="{{ $kat->id }}" {{ (old('kategori_id', $sarana->kategori_id) == $kat->id) ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="jumlah" class="form-control" value="{{ old('jumlah', $sarana->jumlah) }}" min="1" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Kondisi</label>
                            <select name="kondisi" class="form-select">
                                <option value="baik" {{ old('kondisi', $sarana->kondisi) == 'baik' ? 'selected' : '' }}>Baik</option>
                                <option value="rusak_ringan" {{ old('kondisi', $sarana->kondisi) == 'rusak_ringan' ? 'selected' : '' }}>Rusak Ringan</option>
                                <option value="rusak_berat" {{ old('kondisi', $sarana->kondisi) == 'rusak_berat' ? 'selected' : '' }}>Rusak Berat</option>
                                <option value="hilang" {{ old('kondisi', $sarana->kondisi) == 'hilang' ? 'selected' : '' }}>Hilang</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tahun Pengadaan</label>
                            <input type="number" name="tahun_pengadaan" class="form-control" value="{{ old('tahun_pengadaan', $sarana->tahun_pengadaan) }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Lokasi Ruang</label>
                            <input type="text" name="lokasi_ruang" class="form-control" value="{{ old('lokasi_ruang', $sarana->lokasi_ruang) }}">
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Spesifikasi</label>
                            <textarea name="spesifikasi" class="form-control" rows="3">{{ old('spesifikasi', $sarana->spesifikasi) }}</textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Foto Saat Ini</label>
                            @if($sarana->foto)
                                <div><img src="{{ route('files.show', ['path' => $sarana->foto]) }}" width="150" class="img-thumbnail mb-2"></div>
                            @endif
                            <input type="file" name="foto" class="form-control" accept="image/*">
                            <small class="text-muted">Kosongkan jika tidak ingin mengubah foto</small>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-danger bg-opacity-10 border-danger">
            <div class="card-body">
                <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Zona Berbahaya</h6>
                <p class="small">Menghapus sarana akan menghapus semua riwayat peminjaman dan pemeliharaan.</p>
                <button class="btn btn-danger btn-sm" onclick="confirmDelete('{{ route('sarana.destroy', $sarana) }}')">
                    <i class="fas fa-trash"></i> Hapus Sarana Ini
                </button>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none;">
    @csrf @method('DELETE')
</form>
<script>
function confirmDelete(url) {
    if (confirm('Yakin ingin menghapus sarana ini?')) {
        let form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection