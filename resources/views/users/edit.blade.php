@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<x-page-header :title="'Edit User: '.($user->name)">
    <x-slot:actions>
        <a href="{{ route('users.index') }}" class="btn btn-light border">Kembali</a>
    </x-slot:actions>
</x-page-header>

<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-body">
                <form method="POST" action="{{ route('users.update', $user) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Password <span class="text-muted">(kosongkan jika tidak diubah)</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                                <option value="operator" {{ old('role', $user->role) == 'operator' ? 'selected' : '' }}>Staf TU / Operator</option>
                                <option value="guru" {{ old('role', $user->role) == 'guru' ? 'selected' : '' }}>Guru</option>
                                <option value="wali_murid" {{ old('role', $user->role) == 'wali_murid' ? 'selected' : '' }}>Wali Murid</option>
                                <option value="siswa" {{ old('role', $user->role) == 'siswa' ? 'selected' : '' }}>Siswa</option>
                            </select>
                            @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @if(in_array($user->role, ['wali_murid', 'siswa'], true))
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-white py-3">
                <h6 class="mb-0 fw-semibold">Siswa Tertaut</h6>
                <small class="text-muted">Akun ini hanya dapat melihat data siswa di bawah ini lewat Portal Wali.</small>
            </div>
            <div class="card-body">
                <ul class="list-group list-group-flush mb-3">
                    @forelse($user->anak()->orderBy('nama_lengkap')->get() as $s)
                        <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                            <span>{{ $s->nama_lengkap }} <span class="text-muted small">· NIS {{ $s->nis }}</span></span>
                            <form method="POST" action="{{ route('users.siswa.destroy', [$user, $s]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Lepas {{ $s->nama_lengkap }}"><i class="fas fa-link-slash"></i></button>
                            </form>
                        </li>
                    @empty
                        <li class="list-group-item px-0 text-muted">Belum ada siswa yang ditautkan.</li>
                    @endforelse
                </ul>
                <form method="POST" action="{{ route('users.siswa.store', $user) }}" class="row g-2">
                    @csrf
                    <div class="col-sm-8">
                        <input type="text" name="nis" class="form-control @error('nis') is-invalid @enderror" placeholder="Masukkan NIS siswa" value="{{ old('nis') }}" required>
                        @error('nis')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-sm-4 d-grid">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-link me-1"></i> Tautkan</button>
                    </div>
                </form>
            </div>
        </div>
        @endif
    </div>
    <div class="col-md-4">
        <div class="card bg-danger bg-opacity-10 border-danger">
            <div class="card-body">
                <h6 class="text-danger"><i class="fas fa-exclamation-triangle"></i> Zona Berbahaya</h6>
                <p class="small">Menghapus user akan menghapus semua data terkait (log aktivitas, tugas yang dibuat, dll).</p>
                <button class="btn btn-danger btn-sm" onclick="confirmDelete('{{ route('users.destroy', $user) }}', {{ Js::from($user->name) }})">
                    <i class="fas fa-trash"></i> Hapus User Ini
                </button>
            </div>
        </div>
    </div>
</div>

<form id="deleteForm" method="POST" style="display: none;">
    @csrf @method('DELETE')
</form>
<script>
function confirmDelete(url, name) {
    if (confirm('Yakin ingin menghapus user "' + name + '"?')) {
        const form = document.getElementById('deleteForm');
        form.action = url;
        form.submit();
    }
}
</script>
@endsection