@extends('layouts.app')

@section('title', 'Data Guru')

@section('content')
<x-page-header title="Data Guru">
    Kelola data guru. Tambah guru baru lewat form di bawah ini.
</x-page-header>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-white fw-bold">
        <i class="fas fa-user-plus me-1"></i> Tambah Guru Baru
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('guru.store') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Kode Guru <span class="text-danger">*</span></label>
                    <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" value="{{ old('kode') }}" required>
                    @error('kode')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Contoh: 1, 4A, 3/15A (unik)</div>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama') }}" required>
                    @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">NIP</label>
                    <input type="text" name="nip" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip') }}">
                    @error('nip')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">No. Telepon</label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}">
                    @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label">Bidang Studi</label>
                    <select name="bidang_studi" class="form-select @error('bidang_studi') is-invalid @enderror">
                        <option value="">-- Pilih Mata Pelajaran --</option>
                        @foreach($mapels as $mapel)
                            <option value="{{ $mapel->nama_mapel }}" {{ old('bidang_studi') == $mapel->nama_mapel ? 'selected' : '' }}>
                                {{ $mapel->nama_mapel }}
                            </option>
                        @endforeach
                    </select>
                    @error('bidang_studi')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table data-hide-sm="1 3 5" class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">No</th>
                        <th>Kode</th>
                        <th>NIP</th>
                        <th>Nama</th>
                        <th>Bidang Studi</th>
                        <th width="120">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($gurus as $index => $guru)
                    <tr>
                        <td>{{ $index + 1 + ($gurus->currentPage() - 1) * $gurus->perPage() }}</td>
                        <td><span class="badge bg-secondary">{{ $guru->kode }}</span></td>
                        <td>{{ $guru->nip ?? '-' }}</td>
                        <td>
                            <div class="em-identity">
                                <x-avatar :name="$guru->nama" />
                                <span class="fw-bold">{{ $guru->nama }}</span>
                            </div>
                        </td>
                        <td>{{ $guru->bidang_studi ?? '-' }}</td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('guru.edit', $guru) }}" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger" onclick="confirmDelete('{{ route('guru.destroy', $guru) }}', {{ Js::from($guru->nama) }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                        <x-empty-row :colspan="5" icon="fa-folder-open">Belum ada data guru</x-empty-row>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white">
        {{ $gurus->links() }}
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
function confirmDelete(url, name) {
    if (confirm('Yakin ingin menghapus guru "' + name + '"?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection