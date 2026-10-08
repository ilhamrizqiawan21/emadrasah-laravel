@extends('layouts.app')

@section('title', 'Data Kelas')

@section('content')
<x-page-header title="Data Kelas">
    Kelola data kelas. Tambah kelas baru lewat form di bawah ini.
</x-page-header>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="fas fa-plus-circle me-1"></i> Tambah Kelas Baru
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('kelas.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Nama Kelas <span class="text-danger">*</span></label>
                    <input type="text" name="nama_kelas" class="form-control @error('nama_kelas') is-invalid @enderror" value="{{ old('nama_kelas') }}" required>
                    @error('nama_kelas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Contoh: 7A, 8B, 9C</div>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Tingkat <span class="text-danger">*</span></label>
                    <select name="tingkat" class="form-select @error('tingkat') is-invalid @enderror" required>
                        <option value="">-- Pilih Tingkat --</option>
                        @foreach([7, 8, 9] as $t)
                            <option value="{{ $t }}" {{ old('tingkat') == $t ? 'selected' : '' }}>{{ $t }} (Kelas {{ $t }})</option>
                        @endforeach
                    </select>
                    @error('tingkat')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Wali Kelas</label>
                    <select name="guru_pembimbing_id" class="form-select @error('guru_pembimbing_id') is-invalid @enderror">
                        <option value="">-- Pilih Wali Kelas --</option>
                        @foreach($gurus as $guru)
                            <option value="{{ $guru->id }}" {{ old('guru_pembimbing_id') == $guru->id ? 'selected' : '' }}>{{ $guru->nama }}</option>
                        @endforeach
                    </select>
                    @error('guru_pembimbing_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Kapasitas</label>
                    <input type="number" name="kapasitas" class="form-control @error('kapasitas') is-invalid @enderror" value="{{ old('kapasitas', 40) }}">
                    @error('kapasitas')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label">Ruangan</label>
                    <input type="text" name="ruangan" class="form-control @error('ruangan') is-invalid @enderror" value="{{ old('ruangan') }}">
                    @error('ruangan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 d-flex align-items-end justify-content-end">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table data-hide-sm="1 3 5 6" class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Nama Kelas</th>
                        <th>Tingkat</th>
                        <th>Wali Kelas</th>
                        <th>Ruangan</th>
                        <th>Kapasitas</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kelas as $index => $item)
                    <tr class="kelas-row">
                        <td>{{ $index + 1 + ($kelas->currentPage() - 1) * $kelas->perPage() }}</td>
                        <td>
                            <span class="badge bg-primary" style="font-size:14px;">{{ $item->nama_kelas }}</span>
                        </td>
                        <td>{{ $item->tingkat }}</td>
                        <td>{{ $item->guruPembimbing->nama ?? '-' }}</td>
                        <td>{{ $item->ruangan ?? '-' }}</td>
                        <td>{{ $item->kapasitas ?? '0' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('kelas.edit', $item) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('kelas.destroy', $item) }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <x-empty-row :colspan="4" icon="fa-folder-open">Belum ada data kelas</x-empty-row>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $kelas->links() }}
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(url) {
    if (confirm('Yakin ingin menghapus kelas ini? Semua data jadwal yang terkait dengan kelas ini akan ikut terhapus.')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection